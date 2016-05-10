<?php
namespace QueryMap\Component\Filter;

use QueryMap\Component\Map\QueryMapInterface;
use QueryMap\Component\Operator\OperatorInterface;

interface FilterInterface
{
    /**
     * Get the value that is filtered by
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the value that it's filtered by.
     * Must be done after the operator is set
     *
     * @param $value
     * @return FilterInterface
     */
    public function setValue($value);

    /**
     * @param $params
     * @return FilterInterface
     */
    public function setParams($params);

    /**
     * @return array
     */
    public function getParams();

    /**
     * Get the name of the main symbol for this filter
     *
     * @return string
     */
    public function getColumn();

    /**
     * Get alias of the parent query map of this filter
     *
     * @return mixed
     */
    public function getAlias();

    /**
     * Set the operator for this filter
     *
     * @param OperatorInterface $operator
     * @return FilterInterface
     */
    public function setOperator(OperatorInterface $operator);

    /**
     * @return OperatorInterface
     */
    public function getOperator();

    /**
     * Name of the attribute or method
     *
     * @param $name
     * @return FilterInterface
     */
    public function setName($name);

    /**
     * Name of the attribute or method
     *
     * @return string
     */
    public function getName();

    /**
     * Initialize the filter using query map information
     *
     * @param QueryMapInterface $qm
     * @return null
     */
    public function initialize(QueryMapInterface $qm);
}
