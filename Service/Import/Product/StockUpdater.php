<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product;

use \Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Psr\Log\LoggerInterface;

class StockUpdater implements StockUpdaterInterface
{
    private $productRepository;
    private $logger;
    private $stockRegistry;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        StockRegistryInterface $stockRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->stockRegistry = $stockRegistry;
    }

    public function processImport(array $stockDataArray)
    {
        try {
            foreach ($stockDataArray as $stockData) {
                $sku = $stockData['sku'];
                /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
                $stockItem = $this->stockRegistry->getStockItemBySku($sku);

                if ((int)$stockData['qty'] != (int)$stockItem->getData('qty')) {
                    $stockItem->setQty($stockData['qty']);
                    $stockItem->setIsInStock($stockData['qty'] > 0 ? true : false);
                    $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
