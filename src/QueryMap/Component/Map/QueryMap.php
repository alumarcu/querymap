<?php

/*
 * The MIT License (MIT)
 * Copyright (c) 2016 Alexandru Marcu <alumarcu@gmail.com>/DMS Team @ eMAG IT Research
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace QueryMap\Component\Map;

use QueryMap\Component\Filter\FilterInterface;
use QueryMap\Component\Reader\Reader;
use QueryMap\Exception\QueryMapException;

abstract class QueryMap implements QueryMapInterface, QueryMapAdapterInterface
{
    const REUSE_NONE = 0;
    const REUSE_FILTERS = 1;
    const REUSE_OBJECT = 2;
    const REUSE_ALL = 3;

    /** @var array|\QueryMap\Component\Operator\OperatorInterface[] */
    protected $registeredOperators = [];

    /** @var array|\QueryMap\Component\Filter\FilterInterface[] */
    protected $filterStore = [];

    /** @var \QueryMap\Component\Map\QueryMapAdapter */
    protected $adapter;

    /** @var mixed */
    protected $mappedEntity;

    /** @var string */
    protected $alias;

    /** @var array */
    protected $filters = [];

    /** @var array|\QueryMap\Component\Operator\OperatorInterface[] */
    protected static $operatorInst;

    /**
     * Register operators and provide extra setup for the query map.
     */
    abstract protected function configure();

    public function __construct($alias)
    {
        $this->alias = $alias;
        $this->configure();
    }

    /**
     * Add a set of filters to the QueryMap buffer.
     *
     * @param array $filters A key-value array with filters. When a join filter
     *                       is given its value can be another array of filters
     * @param int   $reuse   What should be reused QueryMap::REUSE_* [NONE, FILTERS, OBJECT, ALL]
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function query(array $filters, $reuse = self::REUSE_NONE)
    {
        if ($reuse & self::REUSE_OBJECT === 0) {
            $this->createQuery();
        }

        if ($reuse & self::REUSE_FILTERS > 0) {
            $this->filters = array_merge_recursive($this->filters, $filters);
        } else {
            $this->filters = $filters;
        }

        return $this->compile();
    }

    /**
     * Loads the filters from class annotations.
     *
     * @throws \QueryMap\Exception\QueryMapException
     */
    public function createFilters()
    {
        $reader = $this->getFilterReader();

        if (empty($this->mappedEntity) || !is_object($this->mappedEntity)) {
            throw new QueryMapException(sprintf('%s has an error: mappedEntity not defined!', get_class($this)));
        }

        $fromAttribs = $reader->getAnnotations($this->mappedEntity, Reader::FROM_ATTRIBUTES);
        $fromMethods = $reader->getAnnotations($this, Reader::FROM_PUBLIC_METHODS);

        // If a filter name is defined for both a method and attribute, throw error
        $conflicts = array_intersect_key($fromAttribs, $fromMethods);
        if (!empty($conflicts)) {
            throw new QueryMapException(
                sprintf(
                    '%s has conflicting filter keys: [ %s ]',
                    get_class($this),
                    implode(',', array_keys($conflicts))
                )
            );
        }

        // Otherwise concatenate all filters and return the complete list
        $this->filterStore = array_merge($fromAttribs, $fromMethods);

        // Initialize each filter as needed
        foreach ($this->filterStore as $filter) {
            $filter->initialize($this);
        }
    }

    /**
     * Known filters are added to the engine specific concrete query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function compile()
    {
        foreach ($this->filters as $filterSpec => $filterValue) {
            $nameTokens = explode($this->adapter->getSeparator(), $filterSpec);
            $filterKey = array_shift($nameTokens);

            if (!empty($nameTokens)) {
                $operatorKey = array_shift($nameTokens);
                if (!in_array($operatorKey, array_keys($this->registeredOperators))) {
                    throw new QueryMapException(sprintf('Invalid operator:"%s" in %s', $operatorKey, get_class($this)));
                }

                $operator = $this->registeredOperators[$operatorKey];
            } else {
                $operator = null;
            }

            try {
                /** @var \QueryMap\Component\Filter\FilterInterface $filterObject */
                $filterObject = &$this->filterStore[$filterKey];
                if (empty($filterObject)) {
                    throw new QueryMapException(sprintf('Filter with key "%s" does not exist!', $filterKey));
                }

                $filterObject->setOperator($operator)
                    ->setValue($filterValue)
                    ->setParams($nameTokens);
            } catch (\Exception $e) {
                $message = sprintf('Exception in %s: %s', get_class($this), $e->getMessage());
                throw new QueryMapException($message);
            }

            $this->addToQuery($this->filterStore[$filterKey]);
        }

        return $this->getQuery();
    }

    /**
     * Retrieve a filter from memory.
     *
     * @param $filterKey
     *
     * @return FilterInterface
     */
    public function getFilter($filterKey)
    {
        if (!isset($this->filterStore[$filterKey]) || !($this->filterStore[$filterKey] instanceof FilterInterface)) {
            throw new QueryMapException(sprintf('Filter "%s" does not exist!', $filterKey));
        }

        return $this->filterStore[$filterKey];
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::addToQuery
     *
     * @param FilterInterface $filter
     */
    public function addToQuery(FilterInterface $filter)
    {
        return $this->adapter->addToQuery($filter);
    }

    /**
     * Sets the mapped entity.
     *
     * @param $me
     */
    public function setMappedEntity($me)
    {
        $this->mappedEntity = $me;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::getQuery
     */
    public function getQuery()
    {
        return $this->adapter->getQuery();
    }

    /**
     * @return \QueryMap\Component\Reader\Reader
     */
    public function getFilterReader()
    {
        return $this->adapter->getFilterReader();
    }

    /**
     * @param string $operatorName Namespace and name of the operator class
     *
     * @return QueryMap
     */
    protected function registerOperator($operatorName)
    {
        if (!isset(self::$operatorInst[$operatorName])) {
            /* @var \QueryMap\Component\Operator\OperatorInterface $instance */
            self::$operatorInst[$operatorName] = new $operatorName();
        }

        $instance = self::$operatorInst[$operatorName];
        $this->registeredOperators[$instance->getName()] = $instance;

        return $this;
    }
}
