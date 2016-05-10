<?php
namespace QueryMap\Contrib\Operator;

use QueryMap\Component\Map\QueryMapAdapterInterface;
use QueryMap\Component\Operator\Operator;

class EqualOperator extends Operator
{
    public function getName()
    {
        return 'eq';
    }

    /**
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        if (!is_scalar($value) && !is_null($value)) {
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
            switch (true) {
                case is_scalar($v):
                    return $adapter->prepare("{$f} = :{$f}", array($f => $v));

                case is_null($v):
                    return $adapter->prepare("{$f} IS NULL", array());
            }

            return null;
        };
    }
}
