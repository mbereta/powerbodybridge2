<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\Product\Attribute;

class TextValue extends \Magento\Framework\Model\AbstractModel
{
    
    public function _construct()
    {
        $this->_init(\Powerbody\Bridge\Model\ResourceModel\Product\Attribute\TextValue::class);
    }
    
}
