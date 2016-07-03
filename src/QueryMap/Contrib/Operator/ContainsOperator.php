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

/**
 * Allows searching for text anywhere in a column value
 * as it automatically wraps the search string with % %.
 */
class ContainsOperator extends Operator
{
    public function getName()
    {
        return 'contains';
    }

    /**
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        if (!is_array($value) && !is_string($value) && !is_int($value)) {
            return false;
        }

        if (is_array($value)) {
            foreach ($value as $arrayItem) {
                if (!is_string($arrayItem) && !is_int($arrayItem)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param QueryMapAdapterInterface $adapter
     */
    public function update(QueryMapAdapterInterface $adapter)
    {
        $values = $this->filter->getValue();
        $name = $this->filter->getName();
        $alias = $this->filter->getAlias();

        $paramName = $alias.'#'.$name.'#'.$this->getName();
        
        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $adapter->getQuery();
        
        if (!is_array($values)) {
            $values = [$values];
        }
        
        // TODO: And vs Or.
        // TODO: Separate in two operators.
        foreach ($values as $value) {
            switch (true) {
                case is_string($value):
                    $query->andWhere("{$alias}.{$name} LIKE :{$paramName}")
                        ->setParameter($paramName, $value);
                    break;
                case is_int($value):
                    $query->andWhere("BIT_AND({$alias}.{$name}, :{$paramName}) > 0")
                        ->setParameter($paramName, $value);
                    break;
            }
        }
    }
}
