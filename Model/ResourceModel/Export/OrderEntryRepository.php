<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Export;

use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry;

class OrderEntryRepository implements OrderEntryRepositoryInterface
{

    /** @var \Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface */
    private $configurationReader;

    /** @var \Magento\Sales\Model\OrderFactory */
    private $orderModelFactory;

    /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\CollectionFactory */
    private $statusCollectionFactory;

    /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry */
    private $resourceModel;

    /** var OrderCollectionFactory */
    private $orderCollectionFactory;

    public function __construct(
        \Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface $configurationReader,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\CollectionFactory $statusCollectionFactory,
        \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry $resourceModel,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->configurationReader = $configurationReader;
        $this->orderModelFactory = $orderFactory;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->resourceModel = $resourceModel;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @return \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection
     */
    public function getEntriesToExport() : \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection
    {
        /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection $collection */
        $collection = $this->statusCollectionFactory->create();

        $states = $this->configurationReader->getExportOrderStates();

        $collection->getSelect()
            ->join(
                ['order' => $collection->getTable('sales_order')],
                'order.entity_id = main_table.order_id',
                'status'
            )
            ->where('order.status IN (?)', $states);

        return $collection
            ->addFieldToFilter(
                'main_table.status',
                ['neq' => \Powerbody\Bridge\Model\Export\OrderEntry::STATUS_PUSH]
            )
            ->load();
    }

    public function getOrderCollectionToPush() : \Magento\Sales\Model\ResourceModel\Order\Collection
    {
        /* @var $orderCollection \Magento\Sales\Model\ResourceModel\Order\Collection */
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('main_table.status', ['in' => $this->configurationReader->getUpdateOrderStates()]);

        $orderCollection->getSelect()
            ->joinLeft(
                ['order_entry' => $orderCollection->getTable(OrderEntry::TABLE_NAME)],
                'order_entry.order_id = main_table.entity_id',
                ''
            )
            ->where('order_entry.order_id IS NULL');

        return $orderCollection;
    }

    /**
     * @return \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection
     */
    public function getEntriesToUpdate() : \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection
    {
        /** @var \Powerbody\Bridge\Model\ResourceModel\Export\OrderEntry\Collection $collection */
        $collection = $this->statusCollectionFactory->create();

        $states = $this->configurationReader->getUpdateOrderStates();

        $collection->getSelect()
            ->join(
                ['order' => $collection->getTable('sales_order')],
                'order.entity_id = main_table.order_id',
                'status'
            )
            ->where('order.status IN (?)', $states);

        return $collection
            ->addFieldToFilter(
                'main_table.status',
                ['eq' => \Powerbody\Bridge\Model\Export\OrderEntry::STATUS_PUSH]
            )
            ->load();
    }
    
    public function save(\Powerbody\Bridge\Model\Export\OrderEntry $orderTransferStatus)
    {
        try {
            $this->resourceModel->save($orderTransferStatus);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
    }
    
}
