<?php
namespace QueryMap\Bundle\QueryMapBundle\DependencyInjection;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser as YamlParser;

class QueryMapCompilerPass implements CompilerPassInterface
{
    const MAPPING_PREFIX = 'querymap';

    protected $kernel;

    /**
     * QueryMapCompilerPass constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $configuration = $container->getParameter('querymap.paths');

        if (empty($configuration['paths'])) {
            //No paths configured for querymap mappings
            return;
        }

        $parser = new YamlParser();
        $finder = new Finder();

        foreach ($configuration['paths'] as $configPath) {
            //Use the kernel to find the bundle path
            $path = $this->kernel
                ->locateResource($configPath);

            $finder->files()
                ->in($path);

            //Loop through files on that directory and parse them
            foreach ($finder as $file) {
                /** @var $file \Symfony\Component\Finder\SplFileInfo */
                $rawYmlContent = file_get_contents($file->getPathname());

                $parsedContent = $parser->parse($rawYmlContent);

                foreach ($parsedContent as $key => $value) {
                    $container->setParameter(self::MAPPING_PREFIX.'.'.$key, $value);
                }
            }
        }
    }
}
