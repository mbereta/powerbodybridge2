<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Model\Imported;

use Magento\Framework\Model\AbstractModel;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category as ResourceModel;

class Category extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        $object->setData('updated_date', new \DateTime());
    }
}
