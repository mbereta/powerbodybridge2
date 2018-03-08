<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service;

class TrackService implements TrackServiceInterface
{
    
    /** @var \Magento\Sales\Model\Order\Shipment\TrackFactory */
    private $trackFactory;
    
    /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track */
    private $trackResource;
    
    public function __construct(
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track $trackResource
    ) {
        $this->trackFactory = $trackFactory;
        $this->trackResource = $trackResource;
    }
    
    public function createOrUpdate(\Magento\Sales\Model\Order\Shipment $shipment, array $orderData) : \Magento\Sales\Model\Order\Shipment\Track
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Track[] $tracks */
        $tracks = $shipment->getAllTracks();
        
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = array_shift($tracks);
        
        if (null === $track) {
            $track = $this->trackFactory->create();
        }
        
        $track->addData([
            'order_id' => $shipment->getOrderId(),
            'carrier_code' => 'cc',
            'title' => __('Deliverer'),
            'number' => $orderData['tracking_number'],
        ]);
        $track->setShipment($shipment);
        
        $this->trackResource->save($track);
        
        return $track;
    }
    
}
