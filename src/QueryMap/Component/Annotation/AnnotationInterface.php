<?php
namespace QueryMap\Component\Annotation;

interface AnnotationInterface
{
    /**
     * Get a value from an annotation
     * @param   string    $section      Starts a PHPDoc line and begins with an '@'
     * @param   string    $key          Is the key within brackets which, defined after '@' section
     * @param   mixed   $params
     * @return  string
     */
    public function get($section, $key = null);

    /**
     * Get whether a value exists on an annotated object
     * @param   string    $section      Starts a PHPDoc line and begins with an '@'
     * @param   string    $key          Is the key within brackets which, defined after '@' section
     * @param   mixed   $params
     * @return  string
     */
    public function has($section, $key = null);

    /**
     * @param $value
     * @param $section
     * @param null $key
     * @return mixed
     */
    public function set($value, $section, $key = null);

    /**
     * Creates an annotation from a reflection
     * @param \ReflectionClass $object
     * @return AnnotationInterface
     */
    public function create(\Reflector $object);

    /**
     * Returns the annotation data as stored by the annotation component in use
     * @return mixed
     */
    public function getAnnotationData();

    /**
     * Sets the annotation data stored by the annotation component in use
     * @param $data
     * @return mixed
     */
    public function setAnnotationData($data);
}
