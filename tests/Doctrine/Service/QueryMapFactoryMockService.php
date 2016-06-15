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

namespace QueryMap\Tests\Doctrine\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter;
use QueryMap\Contrib\Service\QueryMapFactory;
use QueryMap\Contrib\Service\QueryMapFactoryInterface;
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

        $connectionParams = [
            'driver' => 'pdo_mysql',
            'host' => TEST_DB_CONNECTION_HOST,
            'dbname' => TEST_DB_CONNECTION_DB_NAME,
            'user' => TEST_DB_CONNECTION_USERNAME,
            'password' => TEST_DB_CONNECTION_PASSWORD,
        ];

        $paths = [QM_TESTS_PATH.'Doctrine/Entity'];
        $config = Setup::createConfiguration($isDevMode = true);

        $driver = new AnnotationDriver(new AnnotationReader(), $paths);

        //Registering no-op annotation autoloader: allow all annotations by default
        AnnotationRegistry::registerLoader('class_exists');
        $config->setMetadataDriverImpl($driver);

        return EntityManager::create($connectionParams, $config);
    }

    public function getCacheDir()
    {
        return;
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
