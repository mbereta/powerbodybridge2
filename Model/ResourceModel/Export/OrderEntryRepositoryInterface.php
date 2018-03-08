<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Export;

interface OrderEntryRepositoryInterface
{
    
    public function getEntriesToExport() : \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection;
    
    public function getEntriesToUpdate() : \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection;
    
    public function save(\Powerbody\Bridge\Model\Export\OrderEntry $orderTransferStatus);
    
}
