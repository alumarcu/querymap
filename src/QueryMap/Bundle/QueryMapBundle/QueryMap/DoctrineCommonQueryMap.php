<?php
namespace QueryMap\Bundle\QueryMapBundle\QueryMap;

use QueryMap\Bundle\QueryMapBundle\QueryMap\Adapter\DoctrineAdapter;
use QueryMap\Contrib\Service\QueryMapFactoryInterface;
use QueryMap\Contrib\Map\CommonQueryMap;

class DoctrineCommonQueryMap extends CommonQueryMap
{
    /**
     * Creates the adapter given an object with correct configuration.
     *
     * @param QueryMapFactoryInterface $configObject
     */
    public function createAdapter(QueryMapFactoryInterface $configObject)
    {
        $this->adapter = new DoctrineAdapter($configObject);
    }
}