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

namespace QueryMap\Bundle\QueryMapBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\FilesystemCache;
use QueryMap\Bundle\QueryMapBundle\QueryMap\DoctrineCommonQueryMap;
use QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter;
use QueryMap\Contrib\Service\QueryMapFactory;
use QueryMap\Contrib\Service\QueryMapFactoryInterface;
use Symfony\Component\HttpKernel\Kernel;

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
     * Returns a generic querymap when no other is defined.
     * 
     * @param $alias
     *
     * @return DoctrineCommonQueryMap
     */
    public function getGenericQueryMap($alias)
    {
        return new DoctrineCommonQueryMap($alias);
    }

    /**
     * @param string|null $connection
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getEntityManager($connection = null)
    {
        return $this->doctrine->getManager($connection);
    }
}
