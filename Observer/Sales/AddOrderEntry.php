<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Observer\Sales;

class AddOrderEntry implements \Magento\Framework\Event\ObserverInterface
{
    
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    
    /** @var \Powerbody\Bridge\Model\Export\OrderEntryFactory */
    private $statusFactory;
    
    /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntryRepository */
    private $statusRepository;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Powerbody\Bridge\Model\Export\OrderEntryFactory $statusFactory,
        \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntryRepository $statusRepository
    ) {
        $this->logger = $logger;
        $this->statusFactory = $statusFactory;
        $this->statusRepository = $statusRepository;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer) : self
    {
        /** @var \Powerbody\Bridge\Model\Export\OrderEntry $status */
        $status = $this->statusFactory->create();
        
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
        
        $status->setData([
            'id' => null,
            'order_id' => $order->getData('entity_id'),
            'status' => \Powerbody\Bridge\Model\Export\OrderEntry::STATUS_NOT_PUSH,
        ]);
        
        try {
            $this->statusRepository->save($status);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        
        return $this;
    }
    
}
