<?php
namespace QueryMap\Contrib\MappingHelper;

use QueryMap\Component\MappingHelper\MappingHelperInterface;
use QueryMap\Exception\QueryMapException;

/**
 * An example of MappingHelper implementation
 */
abstract class MappingHelper implements MappingHelperInterface
{
    /**
     * @inheritdoc
     * @see \QueryMap\Component\MappingHelper\MappingHelperInterface::transform
     */
    public function transform(array $filtersRaw, array $filtersMapping)
    {
        // Here we put filters for QueryMap
        $transformedData = array();

        // If there are filters with higher priority, only those will be considered
        $maxPriority = 0;

        // Loop through the user-provided filters and
        // transform them using the specified policies
        foreach ($filtersRaw as $baseKey => $rawValue) {
            if (!isset($filtersMapping[$baseKey])) {
                continue;   // Filters without a defined policy are skipped
            }

            $policy = $filtersMapping[$baseKey];

            // Verify that the policy is correctly specified and throw exception if not
            try {
                $this->validatePolicy($policy);
            } catch (QueryMapException $e) {
                throw new QueryMapException($baseKey . ': ' .$e->getMessage());
            }

            // Perform each transformation step
            if (!empty($policy['preProcess'])) {
                $filtersRaw[$baseKey] = $this->process($filtersRaw[$baseKey], $policy['preProcess']);
            }

            if (!empty($policy['validate'])) {
                if (!$this->validate($filtersRaw[$baseKey], $policy['validate'])) {
                    unset($filtersRaw[$baseKey]);
                    continue;
                }
            }

            // Now that we know it's valid => check and update priority
            if (!empty($policy['priority'])
                && is_numeric($policy['priority'])
                && $policy['priority'] > 0) {
                $maxPriority = ($policy['priority'] > $maxPriority) ? (int)$policy['priority'] : $maxPriority;
            }
        }

        // Second loop: having max priority calculated only for valid
        // user-provided filters we skip any filters with lower priority
        foreach ($filtersRaw as $baseKey => $rawValue) {
            $policy = $filtersMapping[$baseKey];
            $priority = !empty($policy['priority']) ? (int)$policy['priority'] : 0;

            if ($priority < $maxPriority) {
                continue;  //A valid existing filter has a higher priority
            }

            if (!empty($policy['process'])) {
                $filtersRaw[$baseKey] = $this->process($filtersRaw[$baseKey], $policy['process']);
            }

            // Write the sanitized value to the specified location
            $transformedData = array_merge_recursive(
                $transformedData,
                self::replaceInArray($policy['key'], $filtersRaw[$baseKey])
            );
        }

        return $transformedData;
    }

    /**
     * Recursively replaces a value in a given array with a specified replacement,
     * by default the value to be replaced is null. This is used for placing processed
     * values of filters at their specified location in a QueryMap filter structure.
     *
     * @param array      $array             An array in which to perform the replacement
     * @param mixed      $replacement       A value which should be used as replacement
     * @param null|mixed $valueToReplace    A value to be replaced
     *
     * @return array
     */
    protected static function replaceInArray(array $array, $replacement, $valueToReplace = null)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::replaceInArray($value, $replacement, $valueToReplace);
            }

            if ($valueToReplace === $value) {
                $array[$key] = $replacement;
            }
        }

        return $array;
    }

    /**
     * A 'policy' is a set of rules by which a user-provided field with a value
     * turns into a QueryMap filter. A policy can contain various key/value rules: some
     * optional, some mandatory. This method validates rules on a policy are sufficient.
     *
     * @param array $policy
     * @return mixed
     */
    protected function validatePolicy(array $policy)
    {
        $mandatoryKeys = array('key');

        $diff = array_diff($mandatoryKeys, array_keys($policy));

        if (!empty($diff)) {
            throw new QueryMapException('Mandatory keys are missing from policy: ' . implode(', ', $diff));
        }

        return true;
    }

    /**
     * Performs a given list of transformations for a given value
     * @param mixed     $value      A raw value to be processed
     * @param array     $actions    A list of processing action to apply to the raw value
     * @return mixed
     */
    protected function process($value, array $actions)
    {
        // If the action contains params than it is specified as a key
        // with value being the list of parameters; otherwise the key
        // of the action can be specified directly
        foreach ($actions as $action => $paramsOrAction) {
            if (is_array($paramsOrAction)) {
                // Assume $paramsOrAction is a list of params
                $value = $this->processAction($value, $action, $paramsOrAction);
                continue;
            }
            // Assume $paramsOrAction is the name of the action
            $value = $this->processAction($value, $paramsOrAction);
        }

        return $value;
    }

    /**
     * Performs a given list of validations for a given value
     * @param   mixed     $value          A value to be validated
     * @param   array     $validations    A list of validations to do
     * @return  bool
     */
    protected function validate($value, array $validations)
    {
        // If the validation contains params than it is specified as a key
        // with value being the list of parameters; otherwise the key
        // of the validation can be specified directly
        foreach ($validations as $validation => $paramsOrValidation) {
            if (is_array($paramsOrValidation)) {
                // Assume $paramsOrValidation is a list of params
                if (!$this->performValidation($value, $validation, $paramsOrValidation)) {
                    return false;
                };
            }

            if (!$this->performValidation($value, $paramsOrValidation)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Implements the default set of validations and
     * should also provide the hook for subclasses to extend this
     *
     * @param $value        mixed         The value which to validate
     * @param $validation   mixed         The validation to be performed
     * @param $params       array|mixed   Parameters to use in a validation
     *
     * @return mixed
     */
    abstract protected function performValidation($value, $validation, $params = array());

    /**
     * Implements the default set of processing actions and
     * should also provide the hook for subclasses to extend this
     *
     * @param $value    mixed           The value which to process
     * @param $action   mixed           The processing action to be performed
     * @param $params  array|mixed      Parameters to use in a concrete action
     *
     * @return mixed
     */
    abstract protected function processAction($value, $action, $params = array());
}
