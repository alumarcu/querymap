<?php
namespace QueryMap\Contrib\Annotation;

use \Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationReader;
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
     * @inheritDoc
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
     * @inheritDoc
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
                    return (!empty($annotation->{$key}));
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

        $reusedDoctrineAnnotations = array(
            DoctrineReader::WORD_COLUMN => Column::class,
            DoctrineReader::WORD_DOCTRINE_JOIN_COLUMN => JoinColumn::class,
            DoctrineReader::WORD_DOCTRINE_MANY_TO_ONE => ManyToOne::class,
            DoctrineReader::WORD_DOCTRINE_ONE_TO_ONE => OneToOne::class,
        );

        foreach ($reusedDoctrineAnnotations as $sectionName => $className) {
            if ($sectionName === $section && is_a($annotation, $className)) {
                return true;
            }
        }

        return false;
    }


    /**
     * @inheritDoc
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
