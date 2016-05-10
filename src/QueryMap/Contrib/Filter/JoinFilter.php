<?php
namespace QueryMap\Contrib\Filter;

class JoinFilter extends AttributeFilter
{
    /**
     * Name of the table to join with (i.e. 'delivery')
     * @var string
     */
    protected $with;

    /**
     * Name of the column on which to join with (i.e. 'id')
     * @var string
     */
    protected $on;

    /**
     * Alias of the joined table inside the query (i.e. 'd')
     * @var string
     */
    protected $as;

    /**
     * Name of the QueryMap class of the joined table - so it can be created by name
     * (i.e. '\YourBundle\QueryMap\Foo')
     * @var string
     */
    protected $queryMap;

    /**
     * @var array
     */
    protected $joinOperators;

    public function __construct()
    {
        $this->joinOperators = array(
            'QueryMap\Contrib\Operator\JoinLeftOperator',
            'QueryMap\Contrib\Operator\JoinInnerOperator'
        );
    }

    /**
     * @return string
     */
    public function getWith()
    {
        return $this->with;
    }

    /**
     * @param string $with
     * @return JoinFilter
     */
    public function setWith($with)
    {
        $this->with = $with;

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryMap()
    {
        return $this->queryMap;
    }

    /**
     * @param string $queryMap
     * @return JoinFilter
     */
    public function setQueryMap($queryMap)
    {
        $this->queryMap = $queryMap;

        return $this;
    }

    /**
     * @return string
     */
    public function getOn()
    {
        return $this->on;
    }

    /**
     * @param string $on
     * @return JoinFilter
     */
    public function setOn($on)
    {
        $this->on = $on;

        return $this;
    }

    /**
     * @return string
     */
    public function getAs()
    {
        if (!empty($this->params) && is_array($this->params)) {
            return $this->params[0]; // Select alias override
        }

        return $this->as;
    }

    /**
     * @param string $as
     * @return JoinFilter
     */
    public function setAs($as)
    {
        $this->as = $as;

        return $this;
    }

    /**
     * If this column can be used for joins but none of the expected join operators
     * were used, than this is not a valid join. Might be possible to use as AttributeFilter
     * @return bool
     */
    public function isValid()
    {
        return (in_array(get_class($this->getOperator()), $this->joinOperators));
    }
}
