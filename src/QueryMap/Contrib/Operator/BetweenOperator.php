<?php
namespace QueryMap\Contrib\Operator;

use QueryMap\Component\Map\QueryMapAdapterInterface;
use QueryMap\Component\Operator\Operator;

class BetweenOperator extends Operator
{
    public function getName()
    {
        return 'between';
    }

    /**
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        if (!is_array($value) || count($value) !== 2) {
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
            if (is_numeric($v[0])) {
                $format = '%s BETWEEN %s AND %s';
            } else {
                $format = "%s BETWEEN '%s' AND '%s'";
            }

            return sprintf($format, $f, $v[0], $v[1]);
        };
    }
}
