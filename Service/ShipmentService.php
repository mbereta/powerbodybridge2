<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

class ShipmentService implements ShipmentServiceInterface
{
    
    /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment */
    private $shipmentResource;
    
    /** @var \Magento\Sales\Model\Convert\Order */
    private $convertOrder;
    
    /** @var \Magento\Sales\Model\Convert\Order */
    private $trackService;
    
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Shipment $shipmentResource,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Sales\Model\Order\Config $orderConfig,
        TrackService $trackService
    ) {
        $this->shipmentResource = $shipmentResource;
        $this->convertOrder = $convertOrder;
        $this->trackService = $trackService;
    }
    
    public function createOrUpdate(\Magento\Sales\Model\Order $order, array $orderData) : \Magento\Sales\Model\Order\Shipment
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentsCollection */
        $shipmentsCollection = $order->getShipmentsCollection();
        
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = null;
        
        if (0 === count($shipmentsCollection->getItems())) {
            $shipment = $this->createNewShipment($order);
        } else {
            $shipment = $shipmentsCollection->getFirstItem();
        }
        
        /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track $track */
        $track = $this->trackService->createOrUpdate($shipment, $orderData);
        
        $shipment->addTrack($track)
            ->save();
        
        return $shipment;
    }
    
    private function createNewShipment(\Magento\Sales\Model\Order $order) : \Magento\Sales\Model\Order\Shipment
    {
        if (false === $order->canShip()) {
            throw new \Exception('Order shipping not available');
        }
        
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->convertOrder->toShipment($order);
        
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($order->getAllItems() AS $orderItem) {
            if (0 === $orderItem->getQtyToShip() || true === $orderItem->getIsVirtual()) {
                continue;
            }
            
            $qtyShipped = $orderItem->getQtyToShip();
            $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            
            $shipment->addItem($shipmentItem);
        }
        
        $shipment->register();
        
        $this->shipmentResource->save($shipment);
        
        return $shipment;
    }
    
}
