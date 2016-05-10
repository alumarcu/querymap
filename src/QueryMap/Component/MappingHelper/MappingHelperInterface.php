<?php
namespace QueryMap\Component\MappingHelper;

/**
 * Defines the methods to be implemented by a helper component
 * that maps user-provided input to QueryMap filters
 */
interface MappingHelperInterface
{
    /**
     * Transforms the user-provided (raw) filters using the policies of a given mapping.
     * This method should validate policies, call the hooks corresponding to a policy
     * and return the QueryMap filters corresponding to the user-provided data.
     *
     * @param array $filtersRaw
     * @param array $filtersMapping
     * @return mixed
     */
    public function transform(array $filtersRaw, array $filtersMapping);
}
