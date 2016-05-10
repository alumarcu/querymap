<?php
namespace QueryMap\Contrib\Operator;

use QueryMap\Component\Map\QueryMapAdapterInterface;
use QueryMap\Component\Operator\Operator;

class InOperator extends Operator
{
    public function getName()
    {
        return 'in';
    }

    /**
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        if (!is_array($value)) {
            return false;
        }

        return true;
    }

    public function getCallback(QueryMapAdapterInterface $adapter)
    {
        return function ($f, $v) use ($adapter) {
            return $adapter->prepare("{$f} IN (:{$f})", array($f => $v));
        };
    }
}
