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

use QueryMap\Component\Filter\Filter;
use QueryMap\Component\Map\QueryMapInterface;

class AttributeFilter extends Filter
{
    /**
     * Name used when the query is formed with DQL.
     *
     * @var string
     */
    protected $column;

    protected $name;

    protected $defaultOperator = 'QueryMap\Contrib\Operator\EqualOperator';

    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @see \QueryMap\Component\Filter\FilterInterface::getColumn
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @see \QueryMap\Component\Filter\FilterInterface::initialize
     */
    public function initialize(QueryMapInterface $qm)
    {
        $this->setAlias($qm->getAlias());
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}
