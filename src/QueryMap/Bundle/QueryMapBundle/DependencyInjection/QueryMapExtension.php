<?php
namespace QueryMap\Bundle\QueryMapBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class QueryMapExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        //This returns the application configuration
        $configuration = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('querymap.paths', $configuration);
    }
}
