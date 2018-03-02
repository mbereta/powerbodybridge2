<?php

namespace Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Powerbody\Bridge\Model\Imported\Manufacturer as Model;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer as ResourceModel;

/**
 * Class Collection
 * @package Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
