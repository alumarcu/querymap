<?php
namespace QueryMap\Component\Map;

use QueryMap\Component\Filter\FilterInterface;

/**
 * Defines methods to be implemented by framework specific adapters
 */
interface QueryMapAdapterInterface
{
    /**
     * Replace placeholders marked by ':key' with value
     * where (key => value) is a pair of data
     *
     * @param   string    $queryPart
     * @param   array     $data
     * @return  string
     */
    public function prepare($queryPart, array $data);

    /**
     * Return the framework specific query object
     * (i.e. Zend_Db_Select or Doctrine QueryBuilder)
     *
     * @return mixed
     */
    public function getQuery();

    /**
     * Initialize the framework specific query object
     * (i.e. Zend_Db_Select or Doctrine QueryBuilder)
     *
     * @return mixed|null
     */
    public function createQuery();

    /**
     * Sets the framework specific query object; this is used to
     * provide support for serializing different query maps
     *
     * @param $query
     * @return void
     */
    public function setQuery($query);

    /**
     * Returns the SQL for the current query
     *
     * @return string
     */
    public function getQuerySql();

    /**
     * Provides the logic required to add a FilterInterface object
     * to the concrete framework specific query by considering the
     * actual type the Filter object has
     *
     * @param FilterInterface $filter
     */
    public function addToQuery(FilterInterface $filter);

    /**
     * Calls the callback provided by the filter's operator and
     * returns the resulting condition, eventually adding the alias
     *
     * @param FilterInterface $filter
     * @return mixed
     */
    public function condition(FilterInterface $filter);

    /**
     * Loads a cached annotation using a framework specific cache
     * @param $key
     * @return mixed
     */
    public function loadFromCache($key);

    /**
     * Saves a cached annotation using a framework specific cache
     * @param $key
     * @param $value
     * @return mixed
     */
    public function saveToCache($key, $value);

    /**
     * Return the canonical name of the attribute
     * (i.e. strip underscores from zend model columns)
     *
     * @param $name
     * @return mixed
     */
    public function getCanonicalAttributeName($name);

    /**
     * Adapter for parsing annotations
     * @return \QueryMap\Component\Annotation\AnnotationAdapterInterface
     */
    public function getAnnotationAdapter();

    /**
     * Returns a filter reader suitable for the framework in use
     * @return mixed
     */
    public function getFilterReader();
}
