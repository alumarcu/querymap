<?php
namespace QueryMap\Contrib\Filter;

use QueryMap\Component\Filter\Filter;
use QueryMap\Component\Map\QueryMapInterface;
use QueryMap\Exception\QueryMapException;

class MethodFilter extends Filter
{
    /**
     * The name of the method that will be called, from the
     * query map for which this filter is active and will
     * return a callback which changes the query
     *
     * @var string
     */
    protected $methodName;

    /**
     * The callback should take a MethodFilter as first argument
     * and modify to the query as required
     *
     * @var callable
     */
    protected $callback;

    protected $defaultOperator = 'QueryMap\Contrib\Operator\MethodOperator';

    public function __construct()
    {
        //restrict to default operator since the actual handling
        //is done inside the returned callback anyway
        $this->supportedOperators = array($this->defaultOperator);
    }

    /**
     * Applies the custom filter to the concrete query
     * using framework specific language
     *
     * @return mixed
     */
    public function addToQuery()
    {
        $closure = $this->callback;
        if (!is_callable($closure)) {
            throw new QueryMapException('MethodFilter returned invalid callback or not initialized!');
        }

        return $closure($this);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->methodName;
    }

    /**
     * @param   string          $callback
     * @return  MethodFilter
     */
    public function setName($callback)
    {
        $this->methodName = $callback;

        return $this;
    }

    /**
     * @see \QueryMap\Component\Filter\FilterInterface::initialize
     * @param \QueryMap\Component\Map\QueryMapInterface
     * @return null
     */
    public function initialize(QueryMapInterface $qm)
    {
        $this->setAlias($qm->getAlias());
        //create the actual callback using the query map
        $this->callback = call_user_func(array($qm, $this->methodName));
    }

    /**
     * @see \QueryMap\Component\Filter\FilterInterface::getColumn
     */
    public function getColumn()
    {
        return null;
    }
}
