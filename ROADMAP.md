ROADMAP
=======
Short Term:
* [ ] Implement the bit filter and ability to check if bit exists or is missing. Should accept arrays.
* [ ] The ability to run the same filters twice with different values.
* [ ] Analysis: Getting rid of @QM\Map - what would it take to do it.
* [x] Check use statements and use individual components instead of entire symfony in composer.json
* [x] Fix unit tests and remove dependency to dumpSql/jdorn formatter
* [x] Simplified use, without top level add/fresh/make
    * [x] a) make convenience methods that aggregate these
    * [x] b) these should be correctly named and allow the ability to work with only one call (e.g. $qm->filter([...]))
* [x] Run php code fixer and include heading in all files

Mid Term:
* [ ] Analysis: Should it be possible to combine method operator with another operator (for non-boolean of the method)
* [ ] Analyse whether array_merge_recursive is adequate implementation for add method. Is it ok for single value to become array when add is called twice?
* [ ] Allow dynamically passing actions and validations to the CommonMappingHelper. Add methods like includeValidation($name, $callback).
* [x] Remove dependency on SqlFormatter and remove the dumpSql method
* [x] Improve common interface: clear()->add() should be a common usage pattern (method name "filter" (TBD))

* [ ] Increase **unit tests** coverage
* [ ] Modify existing tests to assert on output of getDQL for unit tests on Doctrine
* [ ] Add **documentation**
* [ ] CommonMappingHelper YAML syntax: New field to specify that: Filter x cancels filter y.

Possible Long Term Features:
* [ ] Allow passing a Symfony entity as a filter and use the Id of that entity
* [x] Split in dedicated repositories with Core objects, Symfony integration and Zend integration
* [x] Reusable and decoupled custom QueryMap methods specified directly when the QueryMap is declared on that entity
