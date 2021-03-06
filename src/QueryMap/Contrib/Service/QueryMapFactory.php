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

namespace QueryMap\Contrib\Service;

use QueryMap\Component\Annotation\Annotation;
use QueryMap\Contrib\Reader\DoctrineReader;

abstract class QueryMapFactory
{
    /**
     * The class of the mapped entity for which a query map was last created.
     *
     * @var string
     */
    protected $currentMappedEntityClass;

    /**
     * The alias of the last created query map.
     *
     * @var string
     */
    protected $currentAlias;

    /**
     * The history of all aliases in the current query map tree which contains
     * alias as key and the value represents the number of times that alias was used.
     * Needed to avoid alias collisions in DQL (since Doctrine further manages its own aliases).
     *
     * @var array<string>
     */
    protected $aliasHistory;

    /**
     * Name of a connection, if something other than default should be used.
     *
     * @var null|string
     */
    protected $connectionName = null;

    /**
     * Returns a generic querymap when no other is defined.
     *
     * @param $alias
     *
     * @return \QueryMap\Contrib\Map\CommonQueryMap
     */
    abstract public function getGenericQueryMap($alias);

    /**
     * @param string|null $connection
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    abstract public function getEntityManager($connection = null);

    /**
     * Returns the annotation adapter.
     *
     * @return \QueryMap\Component\Annotation\AnnotationAdapterInterface
     */
    abstract public function getAnnotationAdapter();

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->getEntityManager($this->getConnectionName())->getRepository($this->currentMappedEntityClass);
    }

    /**
     * {@inheritdoc}
     */
    public function getDb()
    {
        return $this->getEntityManager($this->getConnectionName())->getConnection();
    }

    public function getConnectionName()
    {
        return $this->connectionName;
    }

    public function setConnectionName($connectionName = null)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->currentAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueAlias($alias)
    {
        if (!isset($this->aliasHistory[$alias])) {
            $this->aliasHistory[$alias] = 0;
        }

        ++$this->aliasHistory[$alias];

        if ($this->aliasHistory[$alias] > 1) {
            return $alias.$this->aliasHistory[$alias];
        }

        return $alias;
    }

    /**
     * Prepares a new query map given an entity name and alias.
     *
     * @param string $entityName
     * @param string $alias
     *
     * @return \QueryMap\Contrib\Map\CommonQueryMap
     */
    public function createMap($entityName, $alias)
    {
        // Make sure the alias is unique in subQueryMaps
        $this->currentAlias = $alias;
        $this->currentMappedEntityClass = $entityName;

        $mappedEntity = new $entityName();

        $className = $this->getQueryMapClassNameForEntity($entityName);

        // Return the query map specified in the entity or a generic one
        if (!empty($className) && class_exists($className)) {
            $qm = new $className($alias);
        } else {
            $qm = $this->getGenericQueryMap($alias);
        }

        $qm->setMappedEntity($mappedEntity);

        //TODO: Perhaps check that the trait is applied to a class implementing the interface?
        /* @var $this QueryMapFactoryInterface */
        $qm->createAdapter($this);
        $qm->createFilters();

        return $qm;
    }

    protected function getQueryMapClassNameForEntity($entity)
    {
        /** @var \QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter $annotationAdapter */
        $annotationAdapter = $this->getAnnotationAdapter();

        $reflectionClass = new \ReflectionClass($entity);

        //Retrieve querymap class name from annotation
        $annotation = new Annotation($annotationAdapter);
        $annotation->create($reflectionClass);

        $className = $annotation->get(DoctrineReader::WORD_CLASS_MAP, DoctrineReader::WORD_CLASS_MAP_NAME);

        return $className;
    }

    /**
     * @param $entityName
     * @param $alias
     *
     * @return \QueryMap\Contrib\Map\CommonQueryMap
     */
    public function createMapNew($entityName, $alias)
    {
        // Refresh alias history whenever a new QueryMap is created
        $this->aliasHistory = [];

        $qm = $this->createMap($entityName, $alias);

        return $qm;
    }

    /**
     * @param $entityName
     * @param $alias
     * @param null|string $connection
     *
     * @return \QueryMap\Contrib\Map\CommonQueryMap
     */
    public function create($entityName, $alias, $connection = null)
    {
        $this->setConnectionName($connection);  // Set connection

        $qm = $this->createMapNew($entityName, $alias);

        $this->setConnectionName();  // Reset to default connection

        return $qm;
    }
}
