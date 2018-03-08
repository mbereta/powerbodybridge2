<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service\Export\Task;

class ExportOrders implements TaskInterface
{
    
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    
    /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntryRepository */
    private $orderEntryRepository;
    
    /** @var \Powerbody\Bridge\Service\OrderService */
    private $orderService;
    
    /** @var \Magento\Sales\Model\OrderRepository */
    private $orderRepository;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntryRepository $orderEntryRepository,
        \Powerbody\Bridge\Service\OrderService $orderService,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->logger = $logger;
        $this->orderEntryRepository = $orderEntryRepository;
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
    }
    
    public function run()
    {
        /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection $ordersToExport */
        $ordersToExport = $this->orderEntryRepository->getEntriesToExport();
    
        /** @var \Powerbody\Bridge\Model\Export\OrderEntry $orderEntry */
        foreach ($ordersToExport as $orderEntry) {
            $orderId = (int) $orderEntry->getData('order_id');
            
            try {
                $response = $this->orderService->exportOrderById($orderId);
                
                $this->handleExportReponse($orderEntry, $response);
            } catch (\Exception $e) {
                $this->handleExportException($orderEntry, $e);
            }
        }
    }
    
    protected function handleExportReponse(
        \Powerbody\Bridge\Model\Export\OrderEntry $orderEntry,
        array $response
    ) {
        $result = $response['api_response'] === \Powerbody\Bridge\Api\Client::API_RESPONSE_SUCCESS;
        
        $status = true === $result
            ? \Powerbody\Bridge\Model\Export\OrderEntry::STATUS_PUSH
            : \Powerbody\Bridge\Model\Export\OrderEntry::STATUS_RESPONSE_FAIL;
        
        $responseInfo = $response['api_response'];
        if (false === $result) {
            $responseInfo .= ': ' . $response['api_response_error'];
        }
    
        $orderEntry->addData([
            'status' => $status,
            'response_info' => $responseInfo,
        ]);
        $orderEntry->unsetData('updated_at');
    
        $this->orderEntryRepository->save($orderEntry);
    }
    
    protected function handleExportException(
        \Powerbody\Bridge\Model\Export\OrderEntry $orderEntry,
        \Exception $exception
    ) {
        $orderEntry->addData([
            'status' => \Powerbody\Bridge\Model\Export\OrderEntry::STATUS_RESPONSE_FAIL,
        ]);
        $orderEntry->unsetData('updated_at');
    
        $this->orderEntryRepository->save($orderEntry);
    
        $this->logger->critical($exception);
    }
    
}
