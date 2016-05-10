<?php
namespace QueryMap\Tests\Doctrine\QueryMap;

use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;
use QueryMap\Contrib\Filter\MethodFilter;

class CreatureQueryMap extends DoctrineMockCommonQueryMap
{
    /**
     * @QM\Filter(extraKeys={"creatureArrivedBetween"})
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
