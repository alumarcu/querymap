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

namespace QueryMap\Contrib\Filter;

class JoinFilter extends AttributeFilter
{
    /**
     * Name of the table to join with (i.e. 'delivery').
     *
     * @var string
     */
    protected $with;

    /**
     * Name of the column on which to join with (i.e. 'id').
     *
     * @var string
     */
    protected $on;

    /**
     * Alias of the joined table inside the query (i.e. 'd').
     *
     * @var string
     */
    protected $as;

    /**
     * Name of the QueryMap class of the joined table - so it can be created by name
     * (i.e. '\YourBundle\QueryMap\Foo').
     *
     * @var string
     */
    protected $queryMap;

    /**
     * @var array
     */
    protected $joinOperators;

    public function __construct()
    {
        $this->joinOperators = [
            'QueryMap\Contrib\Operator\JoinLeftOperator',
            'QueryMap\Contrib\Operator\JoinInnerOperator',
        ];
    }

    /**
     * @return string
     */
    public function getWith()
    {
        return $this->with;
    }

    /**
     * @param string $with
     *
     * @return JoinFilter
     */
    public function setWith($with)
    {
        $this->with = $with;

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryMap()
    {
        return $this->queryMap;
    }

    /**
     * @param string $queryMap
     *
     * @return JoinFilter
     */
    public function setQueryMap($queryMap)
    {
        $this->queryMap = $queryMap;

        return $this;
    }

    /**
     * @return string
     */
    public function getOn()
    {
        return $this->on;
    }

    /**
     * @param string $on
     *
     * @return JoinFilter
     */
    public function setOn($on)
    {
        $this->on = $on;

        return $this;
    }

    /**
     * @return string
     */
    public function getAs()
    {
        if (!empty($this->params) && is_array($this->params)) {
            return $this->params[0]; // Select alias override
        }

        return $this->as;
    }

    /**
     * @param string $as
     *
     * @return JoinFilter
     */
    public function setAs($as)
    {
        $this->as = $as;

        return $this;
    }

    /**
     * If this column can be used for joins but none of the expected join operators
     * were used, than this is not a valid join. Might be possible to use as AttributeFilter.
     *
     * @return bool
     */
    public function isValid()
    {
        return in_array(get_class($this->getOperator()), $this->joinOperators);
    }
}
