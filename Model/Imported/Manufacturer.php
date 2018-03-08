<?php

namespace Powerbody\Bridge\Model\Imported;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer as ResourceModel;

/**
 * Class Manufacturer
 * @package Powerbody\Bridge\Model
 */
class Manufacturer extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
