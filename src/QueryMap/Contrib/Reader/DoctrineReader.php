<?php
namespace QueryMap\Contrib\Reader;

use QueryMap\Component\Annotation\Annotation;
use QueryMap\Component\Reader\Reader;
use QueryMap\Contrib\Filter\AttributeFilter;
use QueryMap\Contrib\Filter\JoinFilter;
use QueryMap\Contrib\Filter\MethodFilter;
use QueryMap\Exception\QueryMapException;

class DoctrineReader extends Reader
{
    const WORD_DOCTRINE_JOIN_COLUMN = 'JoinColumn';
    const WORD_DOCTRINE_MANY_TO_ONE = 'ManyToOne';
    const WORD_DOCTRINE_ONE_TO_ONE = 'OneToOne';
    const WORD_DOCTRINE_TARGET_ENTITY = 'targetEntity';
    const WORD_CLASS_MAP = 'Map';
    const WORD_CLASS_MAP_NAME = 'className';
    const WORD_CLASS_MAP_FEATURES = 'features';

    protected function isValidAnnotation($name, Annotation $annotation, $source)
    {
        switch ($source) {
            case static::FROM_ATTRIBUTES:
                return $this->isValidAnnotationOnAttributes($name, $annotation);
            case static::FROM_PUBLIC_METHODS:
                return $this->isValidAnnotationOnMethods($name, $annotation);
        }

        return false;
    }

    protected function processAnnotation($name, Annotation $annotation, $source)
    {
        switch ($source) {
            case static::FROM_ATTRIBUTES:
                return $this->processAnnotationOfAttribute($name, $annotation);
            case static::FROM_PUBLIC_METHODS:
                return $this->processAnnotationOfMethod($name, $annotation);
        }

        return null;
    }

    private function isValidAnnotationOnAttributes($name, Annotation $annotation)
    {
        if (!$annotation->has($this->word(static::WORD_FILTER))) {
            return false;
        }

        if (!$annotation->has($this->word(static::WORD_COLUMN)) &&
            !$annotation->has($this->word(static::WORD_DOCTRINE_JOIN_COLUMN))) {
            return false;
        }

        // If names are missing; we set them manually with attribute name
        if (!$annotation->has($this->word(static::WORD_COLUMN), $this->word(static::WORD_NAME))) {
            $annotation->set($name, $this->word(static::WORD_COLUMN), $this->word(static::WORD_NAME));
        }

        if (!$annotation->has($this->word(static::WORD_DOCTRINE_JOIN_COLUMN), $this->word(static::WORD_NAME))) {
            $annotation->set($name, $this->word(static::WORD_DOCTRINE_JOIN_COLUMN), $this->word(static::WORD_NAME));
        }

        return true;
    }

    private function processAnnotationOfAttribute($name, Annotation $annotation)
    {
        // Accepted keys for both column and joinColumn cases
        $acceptedKeys = array();
        $canonicalAttrName = $this->adapter->getCanonicalAttributeName($name);
        $acceptedKeys[] = $canonicalAttrName;

        if ($annotation->has($this->word(static::WORD_FILTER), $this->word(static::WORD_EXTRA_KEYS))) {
            $extraKeys = $annotation->get($this->word(static::WORD_FILTER), $this->word(static::WORD_EXTRA_KEYS));
            $extraKeys = !is_array($extraKeys) ? array($extraKeys) : $extraKeys;
            $acceptedKeys = array_merge($acceptedKeys, $extraKeys);
        }

        $acceptedKeys = array_unique($acceptedKeys);
        $newFilter = null;

        if ($annotation->has($this->word(static::WORD_COLUMN), $this->word(static::WORD_NAME))) {
            // Has @Column name => Not a join attribute
            $colName = $annotation->get($this->word(static::WORD_COLUMN), $this->word(static::WORD_NAME));
            $acceptedKeys[] = $colName;

            $newFilter = new AttributeFilter();
            $newFilter->setName($canonicalAttrName)
                ->setColumn($colName);

        } elseif ($annotation->has($this->word(static::WORD_DOCTRINE_JOIN_COLUMN), $this->word(static::WORD_NAME))) {
            // @JoinColumn-only
            $colName = $annotation->get($this->word(static::WORD_DOCTRINE_JOIN_COLUMN), $this->word(static::WORD_NAME));
            $acceptedKeys[] = $colName;

            $newFilter = new JoinFilter();
            $newFilter->setName($canonicalAttrName)
                ->setColumn($colName);

            $joinWords = array(
                $this->word(static::WORD_DOCTRINE_MANY_TO_ONE),
                $this->word(static::WORD_DOCTRINE_ONE_TO_ONE)
            );

            foreach ($joinWords as $joinLocation) {
                $targetEntity = $annotation->get($joinLocation, $this->word(static::WORD_DOCTRINE_TARGET_ENTITY));

                if (!empty($targetEntity)) {
                    // This will allow querying the joined table
                    $newFilter->setQueryMap($targetEntity);
                    break;
                }
            }
        } else {
            // Exceptions
            throw new QueryMapException(sprintf('Filter %s has no defined column!', $name));
        }

        // Compile all filters from this Annotation
        $filters = array();
        foreach ($acceptedKeys as $key) {
            if (!is_string($key) || empty($key)) {
                // Show friendly error if a key is not a valid string
                throw new QueryMapException(
                    sprintf(
                        "A filter key for property: '%s' was expected to be a non-empty string, instead:\n%s",
                        $name,
                        print_r($key, true)
                    )
                );
            }
            $filters[$key] = $newFilter;
        }

        return $filters;
    }

    private function isValidAnnotationOnMethods($name, Annotation $annotation)
    {
        if (substr($name, 0, 2) === '__') {
            return false; //assume this is a magic method and ignore
        }

        if (!$annotation->has($this->word(static::WORD_FILTER))) {
            return false;
        }

        return true;
    }

    private function processAnnotationOfMethod($name, Annotation $annotation)
    {
        $acceptedKeys = array();
        $acceptedKeys[] = $name;

        if ($annotation->has($this->word(static::WORD_FILTER), $this->word(static::WORD_EXTRA_KEYS)) &&
            is_array($annotation->get($this->word(static::WORD_FILTER), $this->word(static::WORD_EXTRA_KEYS)))
        ) {
            $acceptedKeys = array_merge(
                $acceptedKeys,
                $annotation->get($this->word(static::WORD_FILTER), $this->word(static::WORD_EXTRA_KEYS))
            );
        }

        $filterInfo = new MethodFilter();
        $filterInfo->setName($name);

        $filters = array();
        foreach ($acceptedKeys as $key) {
            $filters[$key] = $filterInfo;
        }

        return $filters;
    }
}
