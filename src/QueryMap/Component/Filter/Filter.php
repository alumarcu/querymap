<?php
namespace QueryMap\Component\Filter;

use QueryMap\Component\Operator\OperatorInterface;
use QueryMap\Exception\QueryMapException;

abstract class Filter implements FilterInterface
{
    /**
     * An operator to filter with
     * @var \QueryMap\Component\Operator\OperatorInterface
     */
    protected $operator;

    /**
     * Value to filter by
     * @var mixed
     */
    protected $value;

    /**
     * Alias of the parent table
     * @var string
     */
    protected $alias;

    /**
     * Extra parameters for this filter
     * @var array
     */
    protected $params;

    /**
     * If defined as array of class names, it restricts
     * the filter to operators with given classes
     *
     * @var null|array
     */
    protected $supportedOperators = null;

    /**
     * Default operator for a filter type
     *
     * @var null|string
     */
    protected $defaultOperator = null;

    /**
     * @param OperatorInterface $operator
     * @return $this
     */
    public function setOperator(OperatorInterface $operator = null)
    {
        if (null !== $operator &&
            is_array($this->supportedOperators) &&
            !in_array(get_class($operator), $this->supportedOperators)) {
            throw new QueryMapException(
                sprintf(
                    'Filter %s does not support operator: %s',
                    get_class($this),
                    get_class($operator)
                )
            );
        }

        if (null === $operator) {
            $class = $this->defaultOperator;
            $operator = new $class();
        }

        $this->operator = $operator;

        return $this;
    }

    /**
     * @return OperatorInterface
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param $value
     * @return FilterInterface
     * @throws QueryMapException
     */
    public function setValue($value)
    {
        if (empty($this->operator)) {
            throw new QueryMapException('Operator must be set first!');
        }

        if (!$this->operator->supportsValue($value)) {
            throw new QueryMapException(
                sprintf(
                    'Operator %s does not support value: %s',
                    $this->operator->getName(),
                    print_r($value, true)
                )
            );
        }

        $this->value = $value;
        return $this;
    }

    /**
     * @param $params
     * @return FilterInterface
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }
}
