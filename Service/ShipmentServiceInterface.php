<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

interface ShipmentServiceInterface
{
    
    public function createOrUpdate(\Magento\Sales\Model\Order $order, array $orderData) : \Magento\Sales\Model\Order\Shipment;
    
}
