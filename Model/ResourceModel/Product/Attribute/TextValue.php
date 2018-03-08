<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Product\Attribute;

class TextValue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    const TABLE_NAME = 'catalog_product_entity_text';
    const FIELD_ID_NAME = 'value_id';
    
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::FIELD_ID_NAME);
    }
    
}
