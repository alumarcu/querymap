<?php
namespace QueryMap\Component\Map;

interface QueryMapInterface
{
    /**
     * Add a set of filters to the QueryMap buffer
     *
     * @param  array  $filters    A key-value array with filters. When a join filter
     *                            is given its value can be another array of filters
     * @return QueryMapInterface
     */
    public function add(array $filters);

    /**
     * Applies the added set of filters to the QueryMap and returns a query object
     */
    public function make();

    /**
     * Recreates the (engine-specific) query object of the QueryMap (but not the buffer)
     *
     * @return QueryMap
     */
    public function fresh();

    /**
     * Returns the alias of the table
     *
     * @return string
     */
    public function getAlias();

    /**
     * @return \QueryMap\Component\MappingHelper\MappingHelperInterface
     */
    public function getMappingHelper();

    /**
     * Returns the filter buffer (i.e. filters about to be used)
     * @return array
     */
    public function getBuffer();

    /**
     * Resets the filter buffer so that an instance can be reused
     * @return QueryMapInterface
     */
    public function resetBuffer();

    /**
     * Loads the filters from class annotations
     */
    public function createFilters();
}
