<?php
namespace QueryMap\Tests\Doctrine\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use QueryMap\Component\Annotation\Annotation;
use QueryMap\Contrib\Reader\DoctrineReader;
use QueryMap\Contrib\Service\QueryMapFactoryInterface;
use QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter;
use QueryMap\Tests\Doctrine\QueryMap\DoctrineMockCommonQueryMap;

class QueryMapService implements QueryMapFactoryInterface
{
    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    protected static $annotationAdapter;

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

    public function __construct()
    {
        $this->em = $this->createEntityManager();
    }

    public function getEntityManager($connection = null)
    {
        return $this->em;
    }

    protected function createEntityManager()
    {
        $connectionParams = array(
            'driver' => 'pdo_mysql',
            'host' => TEST_DB_CONNECTION_HOST,
            'dbname' => TEST_DB_CONNECTION_DB_NAME,
            'user' => TEST_DB_CONNECTION_USERNAME,
            'password' => TEST_DB_CONNECTION_PASSWORD
        );

        $paths = array(QM_TESTS_PATH . 'Doctrine/Entity');
        $isDevMode = false;
        $config = Setup::createConfiguration($isDevMode, sys_get_temp_dir(), new ArrayCache());
        $driver = new AnnotationDriver(new AnnotationReader(), $paths);
        //registering no-op annotation autoloader - allow all annotations by default
        AnnotationRegistry::registerLoader('class_exists');
        $config->setMetadataDriverImpl($driver);

        return EntityManager::create($connectionParams, $config);
    }

    public function getCacheDir()
    {
        return null;
    }

    public function getAnnotationAdapter()
    {
        if (empty(static::$annotationAdapter)) {
            $annotationReader = new AnnotationReader();

            $adapter = new DoctrineAnnotationAdapter($annotationReader);
            static::$annotationAdapter = $adapter;
        }

        return static::$annotationAdapter;
    }

    public function getGenericQueryMap($alias)
    {
        return new DoctrineMockCommonQueryMap($alias);
    }

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

        //TODO: Perhaps check that the trait is applied to a class implementing the interface?
        /** @var $this QueryMapFactoryInterface */
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
}
