<?php
namespace QueryMap\Tests\Doctrine\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use QueryMap\Contrib\Adapter\DoctrineAdapterConfig;
use QueryMap\Contrib\Adapter\DoctrineAdapterConfigInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter;
use QueryMap\Tests\Doctrine\QueryMap\DoctrineMockCommonQueryMap;

class QueryMapService implements DoctrineAdapterConfigInterface
{
    use DoctrineAdapterConfig;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    protected static $annotationAdapter;

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
        $config = Setup::createConfiguration($isDevMode);
        $driver = new AnnotationDriver(new AnnotationReader(), $paths);
        // registering no-op annotation autoloader - allow all annotations by default
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
}