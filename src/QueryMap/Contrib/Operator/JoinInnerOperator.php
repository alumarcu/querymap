<?php
namespace QueryMap\Contrib\Operator;

use QueryMap\Component\Map\QueryMapAdapterInterface;
use QueryMap\Component\Operator\Operator;

class JoinInnerOperator extends Operator
{
    public function getName()
    {
        return 'ijo';
    }

    /**
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        if (!(is_array($value) || is_null($value))) {
            return false;
        }

        return true;
    }

    public function getCallback(QueryMapAdapterInterface $adapter)
    {
        return null;
    }
}
