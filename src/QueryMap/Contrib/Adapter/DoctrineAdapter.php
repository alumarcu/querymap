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

namespace QueryMap\Contrib\Adapter;

use QueryMap\Component\Filter\FilterInterface;
use QueryMap\Component\Map\QueryMap;
use QueryMap\Component\Map\QueryMapAdapter;
use QueryMap\Component\Reader\Reader;
use QueryMap\Contrib\Filter\AttributeFilter;
use QueryMap\Contrib\Filter\JoinFilter;
use QueryMap\Contrib\Filter\MethodFilter;
use QueryMap\Contrib\Operator\JoinInnerOperator;
use QueryMap\Contrib\Operator\JoinLeftOperator;
use QueryMap\Contrib\Reader\DoctrineReader;
use QueryMap\Exception\QueryMapException;

abstract class DoctrineAdapter extends QueryMapAdapter
{
    /** @var \Doctrine\ORM\QueryBuilder */
    protected $query;

    /** @var \QueryMap\Contrib\Service\QueryMapFactoryInterface */
    protected $configObject;

    /** @var \Doctrine\ORM\EntityRepository */
    protected $repository;

    /**
     * If methods to load/save from cache are not otherwise customized
     * this will be an array cache. It is then assumed cache operations
     * are managed somewhere else.
     *
     * @var array
     */
    protected $tmpCache = [];

    public function __construct($configObject)
    {
        if (!class_exists('\Doctrine\ORM\QueryBuilder')) {
            throw new \Exception('Could not create a DoctrineAdapter instance: cannot find Doctrine libraries!');
        }

        $this->configObject = $configObject;
        $this->repository = $this->configObject->getRepository();
        parent::__construct($this->configObject->getAlias());

        $this->createQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function addToQuery(FilterInterface $filter)
    {
        switch (true) {
            case $filter instanceof JoinFilter && $filter->isValid():
                $joinAlias = $filter->getAs();

                if (empty($joinAlias)) {
                    $joinAlias = $this->configObject->getUniqueAlias($filter->getName());

                    $filter->setAs($joinAlias);
                }

                $filter->update($this);
                $filter->setAs(null); //TODO: Find a better way to pass the alias

                //Handle additional filter inside
                $filterValue = $filter->getValue();
                if (!empty($filterValue) && is_array($filterValue) && $filter->getQueryMap()) {
                    // For doctrine, use the entity class' annotation
                    // to extract the destination query map
                    $targetEntity = $filter->getQueryMap();

                    if (!class_exists($targetEntity)) {
                        throw new QueryMapException(
                            sprintf(
                                'Could not load QueryMap: %s of field: %s',
                                $targetEntity,
                                $filter->getName()
                            )
                        );
                    }

                    /** @var \QueryMap\Contrib\Map\CommonQueryMap $subQueryMap */
                    $subQueryMap = $this->configObject->createMap($targetEntity, $joinAlias);

                    /** @var \Doctrine\ORM\QueryBuilder $query */
                    $query = $subQueryMap
                        ->setQuery($this->getQuery())
                        ->query($filterValue, QueryMap::REUSE_ALL);

                    $this->setQuery($query);
                } elseif (!empty($filterValue) && is_array($filterValue)) {
                    throw new QueryMapException(
                        sprintf(
                            "You did not correctly define a QueryMap for field: '%s'",
                            $filter->getColumn()
                        )
                    );
                }
                break;
            case $filter instanceof AttributeFilter:
                $filter->update($this);
                break;
            case $filter instanceof MethodFilter:
                $filter->addToQuery();
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery()
    {
        $this->query = $this->repository->createQueryBuilder($this->getAlias());
    }

    /**
     * Enables external access to the aliasing logic.
     *
     * @param string $word
     *
     * @return string
     */
    public static function getWordAlias($word)
    {
        switch ($word) {
            case Reader::WORD_EXTRA_KEYS:
                return 'extraKeys';
        }

        return $word;
    }

    /**
     * {@inheritdoc}
     */
    public function getWord($word)
    {
        return static::getWordAlias($word);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromCache($key)
    {
        if (array_key_exists($key, $this->tmpCache)) {
            return $this->tmpCache[$key];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function saveToCache($key, $value)
    {
        $this->tmpCache[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterReader()
    {
        /** @var \QueryMap\Contrib\Reader\DoctrineReader $reader */
        $reader = DoctrineReader::getInstance();
        $reader->setAdapter($this);

        return $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($queryPart, array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                //quote each value individually
                foreach ($value as &$subVal) {
                    $subVal = $this->quote($subVal);
                }
                unset($subVal);
                $quoted = implode(', ', $value);
            } else {
                $quoted = $this->quote($value);
            }

            $queryPart = str_replace(":{$key}", $quoted, $queryPart);
        }

        return $queryPart;
    }

    /**
     * {@inheritdoc}
     *
     * @return \QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter
     */
    public function getAnnotationAdapter()
    {
        return $this->configObject->getAnnotationAdapter();
    }

    /**
     * Doctrine\DBAL\Connection quotes everything without considering the type;
     * this is only to call the quote method only for strings.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function quote($value)
    {
        if ('string' === gettype($value)) {
            return $this->configObject->getDb()->quote($value);
        }

        return $value;
    }
}
