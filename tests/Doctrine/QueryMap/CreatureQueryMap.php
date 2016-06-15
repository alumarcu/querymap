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

namespace QueryMap\Tests\Doctrine\QueryMap;

use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;
use QueryMap\Contrib\Filter\MethodFilter;

class CreatureQueryMap extends DoctrineMockCommonQueryMap
{
    /**
     * @QM\Filter(extraKeys={"creatureArrivedBetween"})
     *
     * @return \Closure
     */
    public function arrivedBetween()
    {
        $qm = &$this;

        return function (MethodFilter $filter) use ($qm) {
            $dates = $filter->getValue();

            $qm->getQuery()
                ->andWhere(
                    sprintf(
                        '(%s.%s BETWEEN :arrivalDateStart AND :arrivalDateEnd)',
                        $qm->alias,
                        'arrivalDate'
                    )
                )
                ->setParameter(':arrivalDateStart', $dates[0])
                ->setParameter(':arrivalDateEnd', $dates[1]);
        };
    }

    /**
     * @QM\Filter
     */
    public function badMethodFilter()
    {
        return "This should raise an error, because it's not a closure";
    }
}
