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

namespace QueryMap\Component\Annotation;

interface AnnotationAdapterInterface
{
    /**
     * Get a value from an annotation.
     *
     * @param AnnotationInterface $annotation An annotation object
     * @param string              $section    Starts a PHPDoc line and begins with an '@'
     * @param string              $key        Is the key within brackets which, defined after '@' section
     *
     * @return string
     */
    public function get(AnnotationInterface $annotation, $section, $key = null);

    /**
     * Get whether a value exists on an annotated object.
     *
     * @param AnnotationInterface $annotation An annotation object
     * @param string              $section    Starts a PHPDoc line and begins with an '@'
     * @param string              $key        Is the key within brackets which, defined after '@' section
     *
     * @return string
     */
    public function has(AnnotationInterface $annotation, $section, $key = null);

    /**
     * Creates an annotation from a reflection.
     *
     * @param \Reflector $object
     *
     * @return AnnotationInterface
     */
    public function create(\Reflector $object);

    /**
     * Sets a key or an entire section for an annotation.
     *
     * @param AnnotationInterface $annotation
     * @param $value
     * @param $section
     * @param null $key
     *
     * @return mixed
     */
    public function set(AnnotationInterface $annotation, $value, $section, $key = null);
}
