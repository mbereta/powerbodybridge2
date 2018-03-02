<?php

namespace Powerbody\Bridge\Model\ResourceModel\Imported\Category;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Powerbody\Bridge\Model\Imported\Category as Model;
use Powerbody\Bridge\Model\ResourceModel\Imported\Category as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    public function toTree()
    {
        $this->load();
        $categoryChildren = array();
        foreach($this->_items as &$item) {
            $categoryChildren[$item['parent_id']][] = &$item;
        }
        unset($item);
        foreach($this->_items as &$item)
        {
            if (isset($categoryChildren[$item['base_category_id']])){
                $item['children'] = $categoryChildren[$item['base_category_id']];
            }
        }
        $categoryChildren[1][key(reset($categoryChildren))]->setData('first', true);

        return $categoryChildren[1];
    }
}
