<?php
namespace QueryMap\Tests\Doctrine\QueryMap;

use QueryMap\Contrib\Adapter\DoctrineAdapterConfigInterface;
use QueryMap\Contrib\Map\CommonQueryMap;

class DoctrineMockCommonQueryMap extends CommonQueryMap
{
    public function __construct($alias)
    {
        $this->alias = $alias;
        $this->configure();
        // createFilters() called outside constructor
    }

    /**
     * Creates the adapter given an object with correct configuration
     * @param DoctrineAdapterConfigInterface $configObject
     */
    public function createAdapter(DoctrineAdapterConfigInterface $configObject)
    {
        $this->adapter = new DoctrineMockAdapter($configObject);
    }
}
