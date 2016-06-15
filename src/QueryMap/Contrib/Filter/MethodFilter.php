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
use QueryMap\Exception\QueryMapException;

class MethodFilter extends Filter
{
    /**
     * The name of the method that will be called, from the
     * query map for which this filter is active and will
     * return a callback which changes the query.
     *
     * @var string
     */
    protected $methodName;

    /**
     * The callback should take a MethodFilter as first argument
     * and modify to the query as required.
     *
     * @var callable
     */
    protected $callback;

    protected $defaultOperator = 'QueryMap\Contrib\Operator\MethodOperator';

    public function __construct()
    {
        //restrict to default operator since the actual handling
        //is done inside the returned callback anyway
        $this->supportedOperators = [$this->defaultOperator];
    }

    /**
     * Applies the custom filter to the concrete query
     * using framework specific language.
     *
     * @return mixed
     */
    public function addToQuery()
    {
        $closure = $this->callback;
        if (!is_callable($closure)) {
            throw new QueryMapException('MethodFilter returned invalid callback or not initialized!');
        }

        return $closure($this);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->methodName;
    }

    /**
     * @param string $callback
     *
     * @return MethodFilter
     */
    public function setName($callback)
    {
        $this->methodName = $callback;

        return $this;
    }

    /**
     * @see \QueryMap\Component\Filter\FilterInterface::initialize
     *
     * @param \QueryMap\Component\Map\QueryMapInterface
     */
    public function initialize(QueryMapInterface $qm)
    {
        $this->setAlias($qm->getAlias());
        //create the actual callback using the query map
        $this->callback = call_user_func([$qm, $this->methodName]);
    }

    /**
     * @see \QueryMap\Component\Filter\FilterInterface::getColumn
     */
    public function getColumn()
    {
        return;
    }
}
