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

interface QueryMapFactoryInterface
{
    /**
     * Factory method for creating a query map with a given connection.
     *
     * @param string $className
     * @param string $tableAlias
     * @param string $connection What to use
     *
     * @return mixed
     */
    public function create($className, $tableAlias, $connection);

    /**
     * Creates a query map and clears the alias history.
     *
     * @param $entityName
     * @param $alias
     *
     * @return mixed
     */
    public function createMapNew($entityName, $alias);

    /**
     * Creates the query map.
     *
     * @param $entityName
     * @param $alias
     *
     * @return mixed
     */
    public function createMap($entityName, $alias);

    /**
     * Returns the Doctrine repository.
     *
     * @return mixed
     */
    public function getRepository();

    /**
     * Returns a database connection required for engine specific operations (e.g. quote).
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getDb();

    /**
     * Returns the alias of the query map that was last created.
     *
     * @return mixed
     */
    public function getAlias();

    /**
     * Returns the path of the cache dir where annotations may be cached.
     *
     * @return mixed
     */
    public function getCacheDir();

    /**
     * If the alias is not unique in the query map tree that was built so far
     * then this method should modify the alias to ensure its uniqueness.
     *
     * @param $alias
     *
     * @return mixed
     */
    public function getUniqueAlias($alias);

    /**
     * @return \QueryMap\Contrib\Annotation\DoctrineAnnotationAdapter
     */
    public function getAnnotationAdapter();
}
