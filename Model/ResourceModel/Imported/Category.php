<?php

namespace Powerbody\Bridge\Model\ResourceModel\Imported;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Category extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('bridge_imported_category', 'id');
    }
    
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        $object->setData('updated_date', new \DateTime());
    }
}
