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

namespace QueryMap\Contrib\Map;

use QueryMap\Component\Filter\FilterInterface;
use QueryMap\Component\Map\QueryMap;
use QueryMap\Component\MappingHelper\MappingHelperInterface;
use QueryMap\Contrib\MappingHelper\CommonMappingHelper;
use QueryMap\Contrib\Service\QueryMapFactoryInterface;

abstract class CommonQueryMap extends QueryMap implements MappingHelperInterface
{
    /** @var \QueryMap\Component\MappingHelper\MappingHelperInterface */
    protected $mappingHelper;

    abstract public function createAdapter(QueryMapFactoryInterface $configObject);

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $toRegister = [
            '\QueryMap\Contrib\Operator\EqualOperator',
            '\QueryMap\Contrib\Operator\NotEqualOperator',
            '\QueryMap\Contrib\Operator\GreaterThanOrEqualOperator',
            '\QueryMap\Contrib\Operator\GreaterThanOperator',
            '\QueryMap\Contrib\Operator\LessThanOperator',
            '\QueryMap\Contrib\Operator\LessThanOrEqualOperator',
            '\QueryMap\Contrib\Operator\InOperator',
            '\QueryMap\Contrib\Operator\LikeOperator',
            '\QueryMap\Contrib\Operator\ContainsOperator',
            '\QueryMap\Contrib\Operator\BetweenOperator',
            '\QueryMap\Contrib\Operator\JoinLeftOperator',
            '\QueryMap\Contrib\Operator\JoinInnerOperator',
            '\QueryMap\Contrib\Operator\MethodOperator',
        ];

        array_map([$this, 'registerOperator'], $toRegister);

        // Register the mapping helper
        $this->mappingHelper = new CommonMappingHelper();
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::prepare
     * {@inheritdoc}
     */
    public function prepare($queryPart, array $data)
    {
        return $this->adapter->prepare($queryPart, $data);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::setQuery
     * {@inheritdoc}
     */
    public function setQuery($query)
    {
        $this->adapter->setQuery($query);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery()
    {
        $this->adapter->createQuery();
    }


    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::loadFromCache
     * {@inheritdoc}
     */
    public function loadFromCache($key)
    {
        return $this->adapter->loadFromCache($key);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::saveToCache
     * {@inheritdoc}
     */
    public function saveToCache($key, $value)
    {
        return $this->adapter->saveToCache($key, $value);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::getCanonicalAttributeName
     * {@inheritdoc}
     */
    public function getCanonicalAttributeName($name)
    {
        return $this->adapter->getCanonicalAttributeName($name);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::getAnnotationAdapter
     * {@inheritdoc}
     */
    public function getAnnotationAdapter()
    {
        return $this->adapter->getAnnotationAdapter();
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::getMappingHelper
     * {@inheritdoc}
     */
    public function getMappingHelper()
    {
        return $this->mappingHelper;
    }

    /**
     * @see \QueryMap\Component\MappingHelper\MappingHelperInterface::transform
     * {@inheritdoc}
     */
    public function transform(array $filtersRaw, array $filtersMapping)
    {
        return $this->mappingHelper->transform($filtersRaw, $filtersMapping);
    }
}
