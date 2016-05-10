<?php
namespace QueryMap\Contrib\Adapter;

use QueryMap\Component\Annotation\Annotation;
use QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter;
use QueryMap\Contrib\Reader\DoctrineReader;

/**
 * Trait to be used in an application side service that creates and manages query maps
 */
trait DoctrineAdapterConfig
{
    /** @var \Doctrine\Bundle\DoctrineBundle\Registry */
    protected $doctrine;

    /**
     * The class of the mapped entity for which a query map was last created
     * @var string
     */
    protected $currentMappedEntityClass;

    /**
     * The alias of the last created query map
     * @var string
     */
    protected $currentAlias;

    /**
     * The history of all aliases in the current query map tree which contains
     * alias as key and the value represents the number of times that alias was used.
     * Needed to avoid alias collisions in DQL (since Doctrine further manages its own aliases)
     * @var array<string>
     */
    protected $aliasHistory;

    /**
     * Name of a connection, if something other than default should be used
     * @var null|string
     */
    protected $connectionName = null;

    /**
     * @inheritdoc
     */
    public function getRepository()
    {
        return $this->getEntityManager($this->getConnectionName())->getRepository($this->currentMappedEntityClass);
    }

    /**
     * @inheritdoc
     */
    public function getDb()
    {
        return $this->getEntityManager($this->getConnectionName())->getConnection();
    }

    public function getConnectionName()
    {
        return $this->connectionName;
    }

    public function setConnectionName($connectionName = null)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * @param string|null $connection
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getEntityManager($connection = null)
    {
        return $this->doctrine->getManager($connection);
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->currentAlias;
    }

    /**
     * @inheritdoc
     */
    public function getUniqueAlias($alias)
    {
        if (!isset($this->aliasHistory[$alias])) {
            $this->aliasHistory[$alias] = 0;
        }

        $this->aliasHistory[$alias]++;

        if ($this->aliasHistory[$alias] > 1) {
            return $alias . $this->aliasHistory[$alias];
        }

        return $alias;
    }

    /**
     * Prepares a new query map given an entity name and alias
     *
     * @param string    $entityName
     * @param string    $alias
     * @return \QueryMap\Contrib\Map\CommonQueryMap
     */
    public function createMap($entityName, $alias)
    {
        // Make sure the alias is unique in subQueryMaps
        $this->currentAlias = $alias;

        $this->currentMappedEntityClass = $entityName;

        /** @var DoctrineAnnotationAdapter $annotationAdapter */
        $annotationAdapter = $this->getAnnotationAdapter();

        $mappedEntity = new $entityName();
        $reflectionClass = new \ReflectionClass($mappedEntity);

        $annotation = new Annotation($annotationAdapter);
        $annotation->create($reflectionClass);

        $className = $annotation->get(DoctrineReader::WORD_CLASS_MAP, DoctrineReader::WORD_CLASS_MAP_NAME);
        // Return the query map specified in the entity or a generic one
        if (!empty($className) && class_exists($className)) {
            $qm = new $className($alias);
        } else {
            $qm = $this->getGenericQueryMap($alias);
        }

        $qm->setMappedEntity($mappedEntity);
        $qm->createAdapter($this);
        $qm->createFilters();

        return $qm;
    }

    /**
     * @param $entityName
     * @param $alias
     * @return \QueryMap\Contrib\Map\CommonQueryMap
     */
    public function createMapNew($entityName, $alias)
    {
        // Refresh alias history whenever a new QueryMap is created
        $this->aliasHistory = array();

        $qm = $this->createMap($entityName, $alias);

        return $qm;
    }

    /**
     * @param $entityName
     * @param $alias
     * @param null|string $connection
     * @return \QueryMap\Contrib\Map\CommonQueryMap
     */
    public function create($entityName, $alias, $connection = null)
    {
        $this->setConnectionName($connection);  // Set connection

        $qm = $this->createMapNew($entityName, $alias);

        $this->setConnectionName();  // Reset to default connection

        return $qm;
    }

    public function getAnnotationAdapter()
    {
        return null;
    }

    /**
     * @return \QueryMap\Contrib\Map\CommonQueryMap
     */
    public function getGenericQueryMap($alias)
    {
        return null;
    }
}
