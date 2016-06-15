CHANGELOG
=========
* 1.1.1 (2016-06-15)
    * changed interface to a single method: query
    * removed dependency on sql-formatter
    * updated unit tests to single method and asserts on DQL

* 1.1.0
    * QueryMap bundle added for simple integration with doctrine
    * dropped support for Zend 1 - to be done by a separate fork
    * removed requirement for labeling all properties with @QM\Filter since it's automatically inferred with doctrine annotations data
    * initial GitHub release

* 1.0.2 (2016-02-18)
    * added resetBuffer and clear methods

* 1.0.0 (2016-02-10)
    * Initial release
    * fixed issue with DoctrineAdapter::dumpSql
