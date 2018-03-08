<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

interface TrackServiceInterface
{
    
    public function createOrUpdate(\Magento\Sales\Model\Order\Shipment $shipment, array $orderData) : \Magento\Sales\Model\Order\Shipment\Track;
    
}
