<?php
namespace QueryMap\Bundle\QueryMapBundle\Service;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Parser as YamlParser;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use QueryMap\Bundle\QueryMapBundle\QueryMap\DoctrineCommonQueryMap;
use QueryMap\Contrib\Service\QueryMapFactory;
use QueryMap\Contrib\Service\QueryMapFactoryInterface;
use QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter;

class QueryMapFactoryService extends QueryMapFactory implements QueryMapFactoryInterface
{
    protected static $annotationAdapter;

    /** @var Kernel */
    protected $kernel;

    /** @var \Doctrine\Bundle\DoctrineBundle\Registry */
    protected $doctrine;

    public function __construct(Kernel $kernel, Registry $doctrine)
    {
        $this->kernel = $kernel;
        $this->doctrine = $doctrine;
    }

    public function getCacheDir()
    {
        return $this->kernel->getCacheDir();
    }

    public function getAnnotationAdapter()
    {
        if (empty(static::$annotationAdapter)) {
            $annotationReader = new CachedReader(
                new AnnotationReader(),
                new FilesystemCache($this->getCacheDir()),
                $debug = false
            );

            $adapter = new DoctrineAnnotationAdapter($annotationReader);
            static::$annotationAdapter = $adapter;
        }

        return static::$annotationAdapter;
    }

    /**
     * Returns a generic querymap when no other is defined
     * 
     * @param $alias
     * @return DoctrineCommonQueryMap
     */
    public function getGenericQueryMap($alias)
    {
        return new DoctrineCommonQueryMap($alias);
    }

    /**
     * @param string|null $connection
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getEntityManager($connection = null)
    {
        return $this->doctrine->getManager($connection);
    }
}
