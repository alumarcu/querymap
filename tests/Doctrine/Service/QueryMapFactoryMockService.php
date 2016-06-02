<?php
namespace QueryMap\Tests\Doctrine\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use QueryMap\Contrib\Service\QueryMapFactory;
use QueryMap\Contrib\Service\QueryMapFactoryInterface;
use QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter;
use QueryMap\Tests\Doctrine\QueryMap\DoctrineMockCommonQueryMap;

class QueryMapFactoryMockService extends QueryMapFactory implements QueryMapFactoryInterface
{
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
        if (!defined('TEST_DB_CONNECTION_HOST') || empty(TEST_DB_CONNECTION_HOST)) {
            throw new \Exception('You should first define `TEST_DB_CONNECTION_HOST` parameter in your phpunit.xml');
        }

        if (!defined('TEST_DB_CONNECTION_DB_NAME') || empty(TEST_DB_CONNECTION_DB_NAME)) {
            throw new \Exception('You should first define `TEST_DB_CONNECTION_DB_NAME` parameter in your phpunit.xml');
        }

        if (!defined('TEST_DB_CONNECTION_USERNAME') || empty(TEST_DB_CONNECTION_USERNAME)) {
            throw new \Exception('You should first define `TEST_DB_CONNECTION_USERNAME` parameter in your phpunit.xml');
        }

        if (!defined('TEST_DB_CONNECTION_PASSWORD') || empty(TEST_DB_CONNECTION_PASSWORD)) {
            throw new \Exception('You should first define `TEST_DB_CONNECTION_PASSWORD` parameter in your phpunit.xml');
        }

        $connectionParams = array(
            'driver' => 'pdo_mysql',
            'host' => TEST_DB_CONNECTION_HOST,
            'dbname' => TEST_DB_CONNECTION_DB_NAME,
            'user' => TEST_DB_CONNECTION_USERNAME,
            'password' => TEST_DB_CONNECTION_PASSWORD
        );

        $paths = array(QM_TESTS_PATH . 'Doctrine/Entity');
        $config = Setup::createConfiguration($isDevMode = true);

        $driver = new AnnotationDriver(new AnnotationReader(), $paths);

        //Registering no-op annotation autoloader: allow all annotations by default
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
