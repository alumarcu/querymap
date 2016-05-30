<?php
namespace QueryMap\Contrib\Filter;

use QueryMap\Component\Filter\Filter;
use QueryMap\Component\Map\QueryMapInterface;

class AttributeFilter extends Filter
{
    /**
     * Name used when the query is formed with DQL
     *
     * @var string
     */
    protected $column;

    protected $name;

    protected $defaultOperator = 'QueryMap\Contrib\Operator\EqualOperator';

    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @see \QueryMap\Component\Filter\FilterInterface::getColumn
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @see \QueryMap\Component\Filter\FilterInterface::initialize
     */
    public function initialize(QueryMapInterface $qm)
    {
        $this->setAlias($qm->getAlias());
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}
