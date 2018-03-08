<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Export;

class OrderEntry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    const TABLE_NAME = 'bridge_export_orderentry';
    const FIELD_ID_NAME = 'entry_id';
    
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::FIELD_ID_NAME);
    }
    
}
