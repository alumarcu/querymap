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

namespace QueryMap\Component\Filter;

use QueryMap\Component\Map\QueryMapAdapterInterface;
use QueryMap\Component\Map\QueryMapInterface;
use QueryMap\Component\Operator\OperatorInterface;

interface FilterInterface
{
    /**
     * Get the value that is filtered by.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the value that it's filtered by.
     * Must be done after the operator is set.
     *
     * @param $value
     *
     * @return FilterInterface
     */
    public function setValue($value);

    /**
     * @param $params
     *
     * @return FilterInterface
     */
    public function setParams($params);

    /**
     * @return array
     */
    public function getParams();

    /**
     * Get the name of the main symbol for this filter.
     *
     * @return string
     */
    public function getColumn();

    /**
     * Get alias of the parent query map of this filter.
     *
     * @return mixed
     */
    public function getAlias();

    /**
     * Set the operator for this filter.
     *
     * @param OperatorInterface $operator
     *
     * @return FilterInterface
     */
    public function setOperator(OperatorInterface $operator);

    /**
     * @param QueryMapAdapterInterface $adapter
     *
     * @return OperatorInterface
     */
    public function update(QueryMapAdapterInterface $adapter);

    /**
     * Name of the attribute or method.
     *
     * @param $name
     *
     * @return FilterInterface
     */
    public function setName($name);

    /**
     * Name of the attribute or method.
     *
     * @return string
     */
    public function getName();

    /**
     * Initialize the filter using query map information.
     *
     * @param QueryMapInterface $qm
     */
    public function initialize(QueryMapInterface $qm);
}
