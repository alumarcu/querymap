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

    public function getGenericQueryMap($alias)
    {
        return new DoctrineCommonQueryMap($alias);
    }

    /**
     * Returns a cache driver. This is used for mappings.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        // TODO: Allow user to define caching parameters
        return new ArrayCache();
    }

    /**
     * Returns a mapping from a configuration file.
     *
     * @param $name     string  Name of the mapping (i.e. AppNameOfBundleBundle:your_mapping)
     * @param $refresh  bool    If set to TRUE, cache is bypassed
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getFilterMapping($name, $refresh = false)
    {
        $cache = $this->getCache();
        $mappingSpec = null;

        if (!$refresh) {
            $mappingSpec = $cache->fetch($name);
        }

        if (!$mappingSpec) {
            $nameTokens = explode(':', $name);

            if (2 !== count($nameTokens)) {
                throw new QueryMapException("Invalid name for mapping: {$name}. Should be: 'bundleName:fileName'");
            }

            $resourcePath = sprintf('@%s/Resources/querymap/%s.yml', $nameTokens[0], $nameTokens[1]);

            try {
                // Find
                $fp = $this->locateResource($resourcePath);

                // Parse
                $content = file_get_contents($fp);
                $mappingSpec = $this->parseContent($content);

                // Cache
                $cache->save($name, $mappingSpec);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $mappingSpec;
    }

    /**
     * Locate a resource.
     *
     * @param $resourcePath
     * @return array|string
     * @throws \Exception
     */
    protected function locateResource($resourcePath)
    {
        return $this->kernel->locateResource($resourcePath);
    }

    protected function parseContent($content)
    {
        return (new YamlParser())->parse($content);
    }
}
