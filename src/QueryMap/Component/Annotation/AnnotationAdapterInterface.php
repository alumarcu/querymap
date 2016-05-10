<?php
namespace QueryMap\Component\Annotation;

interface AnnotationAdapterInterface
{
    /**
     * Get a value from an annotation
     * @param   AnnotationInterface $annotation An annotation object
     * @param   string    $section      Starts a PHPDoc line and begins with an '@'
     * @param   string    $key          Is the key within brackets which, defined after '@' section
     * @return  string
     */
    public function get(AnnotationInterface $annotation, $section, $key = null);

    /**
     * Get whether a value exists on an annotated object
     * @param   AnnotationInterface $annotation An annotation object
     * @param   string    $section      Starts a PHPDoc line and begins with an '@'
     * @param   string    $key          Is the key within brackets which, defined after '@' section
     * @return  string
     */
    public function has(AnnotationInterface $annotation, $section, $key = null);

    /**
     * Creates an annotation from a reflection
     * @param \Reflector $object
     * @return AnnotationInterface
     */
    public function create(\Reflector $object);

    /**
     * Sets a key or an entire section for an annotation
     * @param AnnotationInterface $annotation
     * @param $value
     * @param $section
     * @param null $key
     * @return mixed
     */
    public function set(AnnotationInterface $annotation, $value, $section, $key = null);
}
