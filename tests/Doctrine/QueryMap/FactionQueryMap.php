<?php
namespace QueryMap\Tests\Doctrine\QueryMap;

use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping as QM;
use QueryMap\Contrib\Filter\MethodFilter;

class FactionQueryMap extends DoctrineMockCommonQueryMap
{
    /**
     * @QM\Filter
     * @return \Closure
     */
    public function creatureShareGreaterThan()
    {
        $qm = $this;
        return function (MethodFilter $filter) use ($qm)
        {
            $alias = $qm->getAlias();

            $qm->getQuery()->andWhere("(((cr.netWorth * 100) / {$alias}.netWorth) > :percent)")
                ->setParameter(':percent', $filter->getValue());
        };
    }
}
