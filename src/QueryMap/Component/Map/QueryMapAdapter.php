<?php
namespace QueryMap\Component\Map;

abstract class QueryMapAdapter implements QueryMapAdapterInterface
{
    /** @var mixed */
    protected $query;

    /** @var string */
    protected $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @inheritdoc
     * @param $name
     * @return mixed
     */
    public function getCanonicalAttributeName($name)
    {
        return $name;
    }

    /**
     * @see \QueryMap\Component\Map\QueryMapInterface
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Provides the default separator between filter name and operator
     * @return string
     */
    public function getSeparator()
    {
        return '__';
    }

    /**
     * Defines usage conventions and annotations syntax
     * so that an adapter can change these as required
     * (for example, to reuse existing annotations in Symfony)
     *
     * @var array
     */
    public function getWord($word)
    {
        return $word;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder|Zend_Db_Select
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return null
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }
}
