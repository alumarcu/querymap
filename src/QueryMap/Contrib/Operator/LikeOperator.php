<?php
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
     * @param   QueryMapAdapterInterface $adapter
     * @return  callable
     */
    public function getCallback(QueryMapAdapterInterface $adapter)
    {
        return function ($f, $v) use ($adapter) {
            switch (true) {
                case is_string($v):
                    return $adapter->prepare("{$f} LIKE :{$f}", array($f => $v));
            }

            return null;
        };
    }
}
