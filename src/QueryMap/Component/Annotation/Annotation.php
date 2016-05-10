<?php
namespace QueryMap\Component\Annotation;

class Annotation implements AnnotationInterface
{
    /** @var mixed */
    protected $annotation;

    /** @var AnnotationAdapterInterface */
    protected $adapter;

    public function __construct(AnnotationAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAnnotationData()
    {
        return $this->annotation;
    }

    public function setAnnotationData($data)
    {
        $this->annotation = $data;
    }

    /**
     * @inheritDoc
     */
    public function get($section, $key = null)
    {
        return $this->adapter->get($this, $section, $key);
    }

    /**
     * @inheritDoc
     */
    public function has($section, $key = null)
    {
        return $this->adapter->has($this, $section, $key);
    }

    public function set($value, $section, $key = null)
    {
        return $this->adapter->set($this, $value, $section, $key);
    }

    /**
     * @inheritDoc
     */
    public function create(\Reflector $object)
    {
        $this->annotation = $this->adapter->create($object);
    }
}
