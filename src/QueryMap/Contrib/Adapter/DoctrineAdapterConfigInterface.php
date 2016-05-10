<?php
namespace QueryMap\Contrib\Adapter;

interface DoctrineAdapterConfigInterface
{
    /**
     * Factory method for creating a query map with a given connection
     * @param string    $className
     * @param string    $tableAlias
     * @param string    $connection What to use
     * @return mixed
     */
    public function create($className, $tableAlias, $connection);

    /**
     * Creates a query map and clears the alias history
     * @param $entityName
     * @param $alias
     * @return mixed
     */
    public function createMapNew($entityName, $alias);

    /**
     * Creates the query map
     * @param $entityName
     * @param $alias
     * @return mixed
     */
    public function createMap($entityName, $alias);

    /**
     * Returns the Doctrine repository
     * @return mixed
     */
    public function getRepository();

    /**
     * Returns a database connection required for engine specific operations (e.g. quote)
     * @return \Doctrine\DBAL\Connection
     */
    public function getDb();

    /**
     * Returns the alias of the query map that was last created
     * @return mixed
     */
    public function getAlias();

    /**
     * Returns the path of the cache dir where annotations may be cached
     * @return mixed
     */
    public function getCacheDir();

    /**
     * If the alias is not unique in the query map tree that was built so far
     * then this method should modify the alias to ensure its uniqueness
     *
     * @param $alias
     * @return mixed
     */
    public function getUniqueAlias($alias);

    /**
     * @return \QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter
     */
    public function getAnnotationAdapter();
}
