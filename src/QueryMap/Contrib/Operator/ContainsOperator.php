<?php
namespace QueryMap\Contrib\Operator;

use QueryMap\Component\Map\QueryMapAdapterInterface;
use QueryMap\Component\Operator\Operator;

/**
 * Allows searching for text anywhere in a column value
 * as it automatically wraps the search string with % %
 *
 */
class ContainsOperator extends Operator
{
    public function getName()
    {
        return 'contains';
    }

    /**
     * @see \QueryMap\Component\Operator\OperatorInterface::supportsValue
     */
    public function supportsValue($value)
    {
        if (!is_string($value) && !is_int($value)) {
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
                case (is_string($v) || is_int($v)):
                    return $adapter->prepare("{$f} LIKE :{$f}", array($f => "%{$v}%"));
            }

            return null;
        };
    }
}
