<?php
namespace QueryMap\Contrib\Annotation\DoctrineAnnotationMapping;

use QueryMap\Contrib\Reader\DoctrineReader;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Map implements QueryMapAnnotationInterface
{
    /** @var string */
    public $className;

    /** @var array<string> */
    public $features;

    public function isType($typeName)
    {
        return (DoctrineReader::WORD_CLASS_MAP === $typeName);
    }
}
