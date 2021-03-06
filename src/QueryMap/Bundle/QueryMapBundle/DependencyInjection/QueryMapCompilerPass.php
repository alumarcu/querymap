<?php

/*
 * The MIT License (MIT)
 * Copyright (c) 2016 Alexandru Marcu <alumarcu@gmail.com>/DMS Team @ eMAG IT Research
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace QueryMap\Bundle\QueryMapBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser as YamlParser;

class QueryMapCompilerPass implements CompilerPassInterface
{
    const MAPPING_PREFIX = 'querymap';

    protected $kernel;

    /**
     * QueryMapCompilerPass constructor.
     *
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
                /* @var $file \Symfony\Component\Finder\SplFileInfo */
                $rawYmlContent = file_get_contents($file->getPathname());

                $parsedContent = $parser->parse($rawYmlContent);

                foreach ($parsedContent as $key => $value) {
                    $container->setParameter(self::MAPPING_PREFIX.'.'.$key, $value);
                }
            }
        }
    }
}
