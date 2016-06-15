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

abstract class QueryMapAdapter implements QueryMapAdapterInterface
{
    /** @var mixed */
    protected $query;

    /** @var string */
    protected $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     *
     * @param $name
     *
     * @return mixed
     */
    public function getCanonicalAttributeName($name)
    {
        return $name;
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapInterface
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Provides the default separator between filter name and operator.
     *
     * @return string
     */
    public function getSeparator()
    {
        return '__';
    }

    /**
     * Defines usage conventions and annotations syntax
     * so that an adapter can change these as required
     * (for example, to reuse existing annotations in Symfony).
     *
     * @var array
     */
    public function getWord($word)
    {
        return $word;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }
}
