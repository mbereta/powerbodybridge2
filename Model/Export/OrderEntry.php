<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\Export;

class OrderEntry extends \Magento\Framework\Model\AbstractModel
{
    
    const STATUS_NOT_PUSH = 0;
    const STATUS_PUSH = 1;
    const STATUS_RESPONSE_FAIL = 2;
    
    public function _construct()
    {
        $this->_init(\Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry::class);
    }
    
}
