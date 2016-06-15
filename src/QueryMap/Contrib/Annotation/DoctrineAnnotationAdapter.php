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

namespace QueryMap\Contrib\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use QueryMap\Component\Annotation\AnnotationAdapterInterface;
use QueryMap\Component\Annotation\AnnotationInterface;
use QueryMap\Contrib\Annotation\DoctrineAnnotationMapping\QueryMapAnnotationInterface;
use QueryMap\Contrib\Reader\DoctrineReader;

class DoctrineAnnotationAdapter implements AnnotationAdapterInterface
{
    /** @var \Doctrine\Common\Annotations\Reader */
    protected $annotationReader;

    public function __construct(Reader $annotationReader = null)
    {
        $this->annotationReader = $annotationReader ?: new AnnotationReader();
    }

    /**
     * {@inheritdoc}
     */
    public function get(AnnotationInterface $annotation, $section, $key = null)
    {
        $annotationData = $annotation->getAnnotationData();

        foreach ($annotationData as &$annotation) {
            if ($this->annotationHasValidSection($annotation, $section)) {
                if (null === $key) {
                    return get_object_vars($annotation);
                } else {
                    return $annotation->{$key};
                }
            }
        }
    }

    public function set(AnnotationInterface $annotation, $value, $section, $key = null)
    {
        $annotationData = $annotation->getAnnotationData();

        foreach ($annotationData as &$annotation) {
            if ($this->annotationHasValidSection($annotation, $section)) {
                if (null === $key) {
                    $annotation = $value;
                } else {
                    $annotation->{$key} = $value;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(AnnotationInterface $annotationWrapper, $section, $key = null)
    {
        $annotationData = $annotationWrapper->getAnnotationData();

        foreach ($annotationData as $annotation) {
            if (get_class($annotation) === $section) {
                return true;
            }

            if ($this->annotationHasValidSection($annotation, $section)) {
                // Annotation is known
                if (null === $key) {
                    return true;
                } else {
                    return !empty($annotation->{$key});
                }
            }
        }

        return false;
    }

    protected function annotationHasValidSection($annotation, $section)
    {
        if ($annotation instanceof QueryMapAnnotationInterface && $annotation->isType($section)) {
            return true;
        }

        $reusedDoctrineAnnotations = [
            DoctrineReader::WORD_COLUMN => Column::class,
            DoctrineReader::WORD_DOCTRINE_JOIN_COLUMN => JoinColumn::class,
            DoctrineReader::WORD_DOCTRINE_MANY_TO_ONE => ManyToOne::class,
            DoctrineReader::WORD_DOCTRINE_ONE_TO_ONE => OneToOne::class,
        ];

        foreach ($reusedDoctrineAnnotations as $sectionName => $className) {
            if ($sectionName === $section && is_a($annotation, $className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function create(\Reflector $object)
    {
        $annotation = null;

        if ($object instanceof \ReflectionProperty) {
            $annotation = $this->annotationReader->getPropertyAnnotations($object);
        } elseif ($object instanceof \ReflectionMethod) {
            $annotation = $this->annotationReader->getMethodAnnotations($object);
        } elseif ($object instanceof \ReflectionClass) {
            $annotation = $this->annotationReader->getClassAnnotations($object);
        }

        return $annotation;
    }
}
