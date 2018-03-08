<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Product\Attribute\TextValue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    protected function _construct()
    {
        $this->_init(
            \Powerbody\Bridge\Model\Product\Attribute\TextValue::class,
            \Powerbody\Bridge\Model\ResourceModel\Product\Attribute\TextValue::class
        );
    }
    
}
