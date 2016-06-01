<?php
namespace QueryMap\Bundle\QueryMapBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use QueryMap\Bundle\QueryMapBundle\QueryMap\DoctrineCommonQueryMap;
use QueryMap\Contrib\Adapter\DoctrineAdapterConfigInterface;
use QueryMap\Contrib\Adapter\DoctrineAdapterConfig;
use QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter;
use QueryMap\Exception\QueryMapException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Parser as YamlParser;

class QueryMapFactoryService implements DoctrineAdapterConfigInterface
{
    use DoctrineAdapterConfig;

    protected static $annotationAdapter;

    /** @var Kernel */
    protected $kernel;

    /** @var Registry */
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
}
