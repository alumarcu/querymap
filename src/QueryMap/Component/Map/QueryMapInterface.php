<?php
namespace QueryMap\Component\Map;

interface QueryMapInterface
{
    public function query(array $filters, $reuse = false);

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
     * Loads the filters from class annotations
     */
    public function createFilters();
}
