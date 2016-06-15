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

/**
 * Defines methods to be implemented by framework specific adapters.
 */
interface QueryMapAdapterInterface
{
    /**
     * Replace placeholders marked by ':key' with value
     * where (key => value) is a pair of data.
     *
     * @param string $queryPart
     * @param array  $data
     *
     * @return string
     */
    public function prepare($queryPart, array $data);

    /**
     * Return the framework specific query object.
     *
     * @return mixed
     */
    public function getQuery();

    /**
     * Initialize the framework specific query object.
     *
     * @return mixed|null
     */
    public function createQuery();

    /**
     * Sets the framework specific query object; this is used to
     * provide support for serializing different query maps.
     *
     * @param $query
     */
    public function setQuery($query);

    /**
     * Provides the logic required to add a FilterInterface object
     * to the concrete framework specific query by considering the
     * actual type the Filter object has.
     *
     * @param FilterInterface $filter
     */
    public function addToQuery(FilterInterface $filter);

    /**
     * Calls the callback provided by the filter's operator and
     * returns the resulting condition, eventually adding the alias.
     *
     * @param FilterInterface $filter
     *
     * @return mixed
     */
    public function condition(FilterInterface $filter);

    /**
     * Loads a cached annotation using a framework specific cache.
     *
     * @param $key
     *
     * @return mixed
     */
    public function loadFromCache($key);

    /**
     * Saves a cached annotation using a framework specific cache.
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function saveToCache($key, $value);

    /**
     * Return the canonical name of the attribute
     * (i.e. strip underscores from zend model columns).
     *
     * @param $name
     *
     * @return mixed
     */
    public function getCanonicalAttributeName($name);

    /**
     * Adapter for parsing annotations.
     *
     * @return \QueryMap\Component\Annotation\AnnotationAdapterInterface
     */
    public function getAnnotationAdapter();

    /**
     * Returns a filter reader suitable for the framework in use.
     *
     * @return mixed
     */
    public function getFilterReader();
}
