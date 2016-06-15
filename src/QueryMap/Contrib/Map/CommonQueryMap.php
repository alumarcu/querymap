<?php
namespace QueryMap\Contrib\Map;

use QueryMap\Component\Filter\FilterInterface;
use QueryMap\Component\Map\QueryMap;
use QueryMap\Component\MappingHelper\MappingHelperInterface;
use QueryMap\Contrib\Service\QueryMapFactoryInterface;
use QueryMap\Contrib\MappingHelper\CommonMappingHelper;

abstract class CommonQueryMap extends QueryMap implements MappingHelperInterface
{
    /** @var \QueryMap\Component\MappingHelper\MappingHelperInterface */
    protected $mappingHelper;

    abstract public function createAdapter(QueryMapFactoryInterface $configObject);

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $toRegister = array(
            '\QueryMap\Contrib\Operator\EqualOperator',
            '\QueryMap\Contrib\Operator\NotEqualOperator',
            '\QueryMap\Contrib\Operator\GreaterThanOrEqualOperator',
            '\QueryMap\Contrib\Operator\GreaterThanOperator',
            '\QueryMap\Contrib\Operator\LessThanOperator',
            '\QueryMap\Contrib\Operator\LessThanOrEqualOperator',
            '\QueryMap\Contrib\Operator\InOperator',
            '\QueryMap\Contrib\Operator\LikeOperator',
            '\QueryMap\Contrib\Operator\ContainsOperator',
            '\QueryMap\Contrib\Operator\BetweenOperator',
            '\QueryMap\Contrib\Operator\JoinLeftOperator',
            '\QueryMap\Contrib\Operator\JoinInnerOperator',
            '\QueryMap\Contrib\Operator\MethodOperator',
        );

        array_map(array($this, 'registerOperator'), $toRegister);

        // Register the mapping helper
        $this->mappingHelper = new CommonMappingHelper();
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::prepare
     * @inheritdoc
     */
    public function prepare($queryPart, array $data)
    {
        return $this->adapter->prepare($queryPart, $data);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::setQuery
     * @inheritdoc
     */
    public function setQuery($query)
    {
        $this->adapter->setQuery($query);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createQuery()
    {
        $this->adapter->createQuery();
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::condition
     * @inheritdoc
     */
    public function condition(FilterInterface $filter)
    {
        return $this->adapter->condition($filter);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::loadFromCache
     * @inheritdoc
     */
    public function loadFromCache($key)
    {
        return $this->adapter->loadFromCache($key);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::saveToCache
     * @inheritdoc
     */
    public function saveToCache($key, $value)
    {
        return $this->adapter->saveToCache($key, $value);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::getCanonicalAttributeName
     * @inheritdoc
     */
    public function getCanonicalAttributeName($name)
    {
        return $this->adapter->getCanonicalAttributeName($name);
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::getAnnotationAdapter
     * @inheritdoc
     */
    public function getAnnotationAdapter()
    {
        return $this->adapter->getAnnotationAdapter();
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapAdapterInterface::getMappingHelper
     * @inheritdoc
     */
    public function getMappingHelper()
    {
        return $this->mappingHelper;
    }

    /**
     * @see \QueryMap\Component\MappingHelper\MappingHelperInterface::transform
     * @inheritdoc
     */
    public function transform(array $filtersRaw, array $filtersMapping)
    {
        return $this->mappingHelper->transform($filtersRaw, $filtersMapping);
    }
}
