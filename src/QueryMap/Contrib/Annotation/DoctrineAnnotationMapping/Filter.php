<?php
namespace QueryMap\Contrib\Annotation\DoctrineAnnotationMapping;

use QueryMap\Component\Reader\Reader;

/**
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
class Filter implements QueryMapAnnotationInterface
{
    /** @var array<string> */
    public $extraKeys;

    public function isType($typeName)
    {
        return (Reader::WORD_FILTER === $typeName);
    }
}
