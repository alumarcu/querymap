<?php
namespace QueryMap\Contrib\Operator;

use QueryMap\Component\Map\QueryMapAdapterInterface;
use QueryMap\Component\Operator\Operator;

class MethodOperator extends Operator
{
    public function getName()
    {
        return 'method';
    }

    /**
     * Method filters support all values
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        return true;
    }

    public function getCallback(QueryMapAdapterInterface $adapter)
    {
        return null;
    }
}
