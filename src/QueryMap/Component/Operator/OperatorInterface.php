<?php
namespace QueryMap\Component\Operator;

use \QueryMap\Component\Map\QueryMapAdapterInterface;

interface OperatorInterface
{
    /**
     * Name of the operator
     * @return string
     */
    public function getName();

    /**
     * Returns a callable method that provides the
     * conditional part of a query given a filter
     * name and a value.
     *
     * @param \QueryMap\Component\Map\QueryMapAdapterInterface $adapter
     * @return callable
     */
    public function getCallback(QueryMapAdapterInterface $adapter);

    /**
     * Whether a given operator allows a specific value or not
     * (i.e. EqualOperator cannot have an array as value)
     *
     * @param $value
     * @return bool
     */
    public function supportsValue($value);
}
