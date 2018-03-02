<?php

namespace Powerbody\Bridge\Model\ResourceModel\Imported;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Manufacturer
 * @package Powerbody\Bridge\Model\ResourceModel
 */
class Manufacturer extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('bridge_imported_manufacturer', 'id');
    }
    
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        $object->setData('updated_date', new \DateTime());
    }
}
