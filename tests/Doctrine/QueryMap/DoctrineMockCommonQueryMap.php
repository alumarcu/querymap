<?php
namespace QueryMap\Tests\Doctrine\QueryMap;

use QueryMap\Contrib\Service\QueryMapFactoryInterface;
use QueryMap\Contrib\Map\CommonQueryMap;

class DoctrineMockCommonQueryMap extends CommonQueryMap
{
    /**
     * Creates the adapter given an object with correct configuration
     * @param QueryMapFactoryInterface $configObject
     */
    public function createAdapter(QueryMapFactoryInterface $configObject)
    {
        $this->adapter = new DoctrineMockAdapter($configObject);
    }
}
