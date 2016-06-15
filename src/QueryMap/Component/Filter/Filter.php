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

use QueryMap\Component\Operator\OperatorInterface;
use QueryMap\Exception\QueryMapException;

abstract class Filter implements FilterInterface
{
    /**
     * An operator to filter with.
     *
     * @var \QueryMap\Component\Operator\OperatorInterface
     */
    protected $operator;

    /**
     * Value to filter by.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Alias of the parent table.
     *
     * @var string
     */
    protected $alias;

    /**
     * Extra parameters for this filter.
     *
     * @var array
     */
    protected $params;

    /**
     * If defined as array of class names, it restricts
     * the filter to operators with given classes.
     *
     * @var null|array
     */
    protected $supportedOperators = null;

    /**
     * Default operator for a filter type.
     *
     * @var null|string
     */
    protected $defaultOperator = null;

    /**
     * @param OperatorInterface $operator
     *
     * @return $this
     */
    public function setOperator(OperatorInterface $operator = null)
    {
        if (null !== $operator &&
            is_array($this->supportedOperators) &&
            !in_array(get_class($operator), $this->supportedOperators)) {
            throw new QueryMapException(
                sprintf(
                    'Filter %s does not support operator: %s',
                    get_class($this),
                    get_class($operator)
                )
            );
        }

        if (null === $operator) {
            $class = $this->defaultOperator;
            $operator = new $class();
        }

        $this->operator = $operator;

        return $this;
    }

    /**
     * @return OperatorInterface
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param $value
     *
     * @throws QueryMapException
     *
     * @return FilterInterface
     */
    public function setValue($value)
    {
        if (empty($this->operator)) {
            throw new QueryMapException('Operator must be set first!');
        }

        if (!$this->operator->supportsValue($value)) {
            throw new QueryMapException(
                sprintf(
                    'Operator %s does not support value: %s',
                    $this->operator->getName(),
                    print_r($value, true)
                )
            );
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @param $params
     *
     * @return FilterInterface
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }
}
