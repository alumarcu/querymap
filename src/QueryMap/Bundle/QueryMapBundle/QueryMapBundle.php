<?php
namespace QueryMap\Bundle\QueryMapBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;
use QueryMap\Bundle\QueryMapBundle\DependencyInjection\QueryMapCompilerPass;

class QueryMapBundle extends Bundle
{
    protected $kernel;

    /**
     * QueryMapBundle constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new QueryMapCompilerPass($this->kernel));
    }
}
