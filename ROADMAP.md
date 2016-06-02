ROADMAP
=======
* Short Term:
    - Check use statements and use individual components instead of entire symfony in composer.json
    - Fix unit tests and remove dependency to dumpSql/jdorn formatter
    - Simplified use, without top level add/fresh/make
        - a) make convenience methods that aggregate these
        - b) these should be correctly named and allow the ability to work with only one call (e.g. $qm->filter([...]))
    - Run php code fixer and include heading in all files

* Short-Mid Term:
    - Remove dependency on SqlFormatter and remove the dumpSql method
    - Allow dynamically passing actions and validations to the CommonMappingHelper. Add methods like includeValidation($name, $callback).
    - Improve common interface: clear()->add() should be a common usage pattern (method name "filter" (TBD))
    - Analyse whether array_merge_recursive is adequate implementation for add method. Is it ok for single value to become array when add is called twice?
    - Increase **unit tests** coverage
    - Modify existing tests to assert on output of getDQL for unit tests on Doctrine
    - Add **documentation**
    - CommonMappingHelper YAML syntax: New field to specify that: Filter x cancels filter y.

* Possible Mid-Long Term Features:
    - Split in dedicated repositories with Core objects, Symfony integration and Zend integration
    - Allow passing a Symfony entity as a filter and use the Id of that entity
    - Reusable and decoupled custom QueryMap methods specified directly when the QueryMap is declared on that entity