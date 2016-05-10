<?php
namespace QueryMap\Contrib\Operator;

use QueryMap\Component\Operator\Operator;
use QueryMap\Component\Map\QueryMapAdapterInterface;

class LessThanOrEqualOperator extends Operator
{
    public function getName()
    {
        return 'lte';
    }

    /**
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        if (!is_scalar($value)) {
            return false;
        }

        return true;
    }

    /**
     * @see     \QueryMap\Component\Map\QueryMapAdapterInterface::getCallback
     * @param   QueryMapAdapterInterface $adapter
     * @return  callable
     */
    public function getCallback(QueryMapAdapterInterface $adapter)
    {
        return function ($f, $v) use ($adapter) {
            return $adapter->prepare("{$f} <= :{$f}", array($f => $v));
        };
    }
}
