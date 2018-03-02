<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Task;

class UpdateOrders implements TaskInterface
{
    
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    
    /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntryRepository */
    private $exportOrderEntryRepository;
    
    /** @var \Powerbody\Bridge\Service\OrderServiceInterface */
    private $orderSyncService;
    
    /** @var \Magento\Sales\Model\OrderRepository */
    private $orderRepository;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntryRepositoryInterface $exportOrderEntryRepository,
        \Powerbody\Bridge\Service\OrderServiceInterface $orderSyncService,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->logger = $logger;
        $this->exportOrderEntryRepository = $exportOrderEntryRepository;
        $this->orderSyncService = $orderSyncService;
        $this->orderRepository = $orderRepository;
    }
    
    public function run()
    {
        $this->logger->debug(__('Started orders update:') . date('Y-m-d H:i:s', time()));

        /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection */
        $entriesToUpdate = $this->exportOrderEntryRepository->getEntriesToUpdate();
        
        $orderIds = [];
    
        /** @var \Powerbody\Bridge\Model\Export\OrderEntry $entry */
        foreach ($entriesToUpdate as $entry) {
            $orderIds[] = $entry->getData('order_id');
        }
    
        if (false === empty($orderIds)) {
            try {
                $this->orderSyncService->updateOrders($orderIds);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        $this->logger->debug(__('Ended orders update:') . date('Y-m-d H:i:s', time()));
    }
    
}
