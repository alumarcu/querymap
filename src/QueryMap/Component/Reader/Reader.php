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

namespace QueryMap\Component\Reader;

use QueryMap\Component\Annotation\Annotation;
use QueryMap\Component\Map\QueryMapAdapter;
use QueryMap\Exception\QueryMapException;

abstract class Reader
{
    const FROM_ATTRIBUTES = 1;
    const FROM_PUBLIC_METHODS = 12;

    /**
     * Constants below define default annotation syntax and can be
     * aliased by implementing a getWord() method in a concrete adapter.
     */
    const WORD_FILTER = 'Filter';
    const WORD_COLUMN = 'Column';
    const WORD_JOIN_FILTER = 'JoinFilter';
    const WORD_JOINED_QUERY_MAP = 'JoinedQueryMap';

    const WORD_NAME = 'name';
    const WORD_EXTRA_KEYS = 'extraKeys';
    const WORD_WITH = 'with';
    const WORD_ON = 'on';
    const WORD_AS = 'as';

    protected $annotationCache = [];

    /** @var \QueryMap\Component\Map\QueryMapAdapter */
    protected $adapter;

    /** @var Reader */
    protected static $instance = null;

    /**
     * @param            $name
     * @param Annotation $annotation
     * @param            $source
     *
     * @return bool
     */
    abstract protected function isValidAnnotation($name, Annotation $annotation, $source);

    /**
     * @param string     $name
     * @param Annotation $annotation
     * @param int        $source
     *
     * @return array
     */
    abstract protected function processAnnotation($name, Annotation $annotation, $source);

    /**
     * @return Reader
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Get annotations for an object's attributes or properties.
     *
     * @param $object
     * @param $source
     *
     * @throws \QueryMap\Exception\QueryMapException
     *
     * @return array|bool
     */
    public function getAnnotations($object, $source)
    {
        if (!$this->getCachedAnnotation($object, $source)) {
            $reflectionObject = new \ReflectionClass($object);

            switch ($source) {
                case self::FROM_ATTRIBUTES:
                    $annotatedObjects = $reflectionObject->getProperties();
                    break;
                case self::FROM_PUBLIC_METHODS:
                    $annotatedObjects = $reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC);
                    break;
                default:
                    throw new QueryMapException('getAnnotations: Invalid argument provided for source!');
            }

            $this->setCachedAnnotation($object, $source, $this->getAnnotationsFromObjects($annotatedObjects, $source));
        }

        return $this->getCachedAnnotation($object, $source);
    }

    /**
     * @param \QueryMap\Component\Map\QueryMapAdapter $adapter
     */
    public function setAdapter(QueryMapAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Creates a key for the cache based on the class name and the
     * type of annotations that were read (i.e. from attributes or methods).
     *
     * @param $object
     * @param $source
     *
     * @return string
     */
    private function getCacheKey($object, $source)
    {
        return md5(sprintf('%s_%s', get_class($object), $source));
    }

    /**
     * Save the annotation to cache.
     *
     * @param $object
     * @param $source
     * @param $content
     */
    public function setCachedAnnotation($object, $source, $content)
    {
        $cacheKey = $this->getCacheKey($object, $source);

        $this->adapter->saveToCache($cacheKey, $content);

        $this->annotationCache[get_class($object)][$source] = $content;
    }

    /**
     * Get annotation from cache.
     *
     * @param $object
     * @param $source
     *
     * @return bool|array
     */
    public function getCachedAnnotation($object, $source)
    {
        //initially check in memory cache
        if (isset($this->annotationCache[get_class($object)][$source])) {
            return $this->annotationCache[get_class($object)][$source];
        }

        //check if cached and load to memory
        $cacheKey = $this->getCacheKey($object, $source);
        if ($content = $this->adapter->loadFromCache($cacheKey)) {
            $this->annotationCache[get_class($object)][$source] = $content;

            return $content;
        }

        //not found in any cache
        return false;
    }

    /**
     * @param \ReflectionClass[]|array $objects
     * @param                          $source
     *
     * @return Annotation[]
     */
    protected function getAnnotationsFromObjects(array $objects, $source)
    {
        $annotations = [];

        foreach ($objects as $object) {
            $annotation = $this->createAnnotation($object);

            if (!$this->isValidAnnotation($object->getName(), $annotation, $source)) {
                continue;
            }

            $annotations = array_merge(
                $annotations,
                $this->processAnnotation($object->getName(), $annotation, $source)
            );
        }

        return $annotations;
    }

    protected function createAnnotation($object)
    {
        $annotation = new Annotation($this->adapter->getAnnotationAdapter());

        $annotation->create($object);

        return $annotation;
    }

    protected function word($word)
    {
        return $this->adapter->getWord($word);
    }

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    protected function __wakeup()
    {
    }
}
