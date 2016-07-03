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

namespace QueryMap\Contrib\Operator;

use QueryMap\Component\Map\QueryMapAdapterInterface;
use QueryMap\Component\Operator\Operator;

class LikeOperator extends Operator
{
    public function getName()
    {
        return 'like';
    }

    /**
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        if (!is_string($value)) {
            return false;
        }

        return true;
    }

    /**
     * @see     \QueryMap\Component\Map\QueryMapAdapterInterface::getCallback
     *
     * @param QueryMapAdapterInterface $adapter
     *
     * @return callable
     */
    public function getCallback(QueryMapAdapterInterface $adapter)
    {
        return function ($f, $v) use ($adapter) {
            switch (true) {
                case is_string($v):
                    return $adapter->prepare("{$f} LIKE :{$f}", [$f => $v]);
            }

            return;
        };
    }

    public function update(QueryMapAdapterInterface $adapter)
    {
        $value = $this->filter->getValue();
        $name = $this->filter->getName();
        $alias = $this->filter->getAlias();

        $paramName = $alias.'#'.$name.'#'.$this->getName();

        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $adapter->getQuery();

        $query->andWhere("{$alias}.{$name} LIKE :{$paramName}")
            ->setParameter($paramName, $value);
    }
}
