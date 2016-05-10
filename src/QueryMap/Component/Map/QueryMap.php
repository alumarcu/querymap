<?php
namespace QueryMap\Component\Map;

use QueryMap\Component\Reader\Reader;
use QueryMap\Component\Filter\FilterInterface;
use QueryMap\Exception\QueryMapException;

abstract class QueryMap implements QueryMapInterface, QueryMapAdapterInterface
{
    /** @var array|\QueryMap\Component\Operator\OperatorInterface[] */
    protected $registeredOperators = array();

    /** @var array|\QueryMap\Component\Filter\FilterInterface[] */
    protected $filterStore = array();

    /** @var \QueryMap\Component\Map\QueryMapAdapter */
    protected $adapter;

    /** @var mixed */
    protected $mappedEntity;

    /** @var string */
    protected $alias;

    /** @var array */
    protected $filters = array();

    /** @var array|\QueryMap\Component\Operator\OperatorInterface[] */
    static protected $operatorInst;

    /**
     * Register operators and provide extra setup for the query map
     */
    abstract protected function configure();

    public function __construct($alias)
    {
        $this->alias = $alias;
        $this->configure();
        $this->createFilters();
    }

    /**
     * @inheritdoc
     * @see \QueryMap\Component\Map\QueryMapInterface::add
     */
    public function add(array $filters)
    {
        $this->filters = array_merge_recursive($this->filters, $filters);

        return $this;
    }

    /**
     * Loads the filters from class annotations
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
     * @inheritdoc
     * @see \QueryMap\Component\Map\QueryMapInterface::make
     */
    public function make()
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
     * @inheritdoc
     */
    public function fresh()
    {
        $this->createQuery();

        return $this;
    }

    /**
     * Retrieve a filter from memory
     *
     * @param $filterKey
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
     * @param FilterInterface $filter
     */
    public function addToQuery(FilterInterface $filter)
    {
        return $this->adapter->addToQuery($filter);
    }

    /**
     * Sets the mapped entity
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
     * @return array
     */
    public function getBuffer()
    {
        return $this->filters;
    }

    /**
     * @inheritdoc
     */
    public function resetBuffer()
    {
        $this->filters = array();

        return $this;
    }

    /**
     * Aggregates fresh and resetBuffer methods, clearing both
     * the query object instance and the filters that were set
     */
    public function clear()
    {
        return $this
            ->fresh()
            ->resetBuffer();
    }

    /**
     * @return \QueryMap\Component\Reader\Reader
     */
    public function getFilterReader()
    {
        return $this->adapter->getFilterReader();
    }

    /**
     * @param   string    $operatorName Namespace and name of the operator class
     * @return  QueryMap
     */
    protected function registerOperator($operatorName)
    {
        if (!isset(self::$operatorInst[$operatorName])) {
            /** @var \QueryMap\Component\Operator\OperatorInterface $instance */
            self::$operatorInst[$operatorName] = new $operatorName();
        }

        $instance = self::$operatorInst[$operatorName];
        $this->registeredOperators[$instance->getName()] = $instance;

        return $this;
    }
}
