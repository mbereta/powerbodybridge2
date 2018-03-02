<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    protected function _construct()
    {
        $this->_init(
            \Powerbody\Bridge\Model\Export\OrderEntry::class,
            \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry::class
        );
    }
    
}
