<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product;

use \Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

class StockUpdater implements StockUpdaterInterface
{
    private $productRepository;
    private $logger;
    private $stockRegistry;
    protected $_sourceItemsSaveInterface;
    protected $_sourceItemFactory;


    public function __construct(
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        StockRegistryInterface $stockRegistry,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        SourceItemInterfaceFactory $sourceItemFactory
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->stockRegistry = $stockRegistry;
        $this->_sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->_sourceItemFactory = $sourceItemFactory;
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

                $id = $stockItem->getProductId();
                $productModel = $this->productRepository->getById($id);
                $this->updateSourcesItemForProductBySku($productModel, $stockData);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    private function updateSourcesItemForProductBySku(Product $productModel, array $stockDataArray)
    {

        $this->updateSourceItemForProductBySku($productModel->getSku(), (float)$stockDataArray['qty'], 'pb');

        if ('Stock local' != $productModel->getAttributeText('stock')) {
            $this->updateSourceItemForProductBySku($productModel->getSku(), (float)$stockDataArray['qty'], 'local');
        }

    }

    private function updateSourceItemForProductBySku(string $sku, float $qty, string $code = 'pb')
    {

        $sourceItem = $this->_sourceItemFactory->create();
        $sourceItem->setSourceCode($code);
        $sourceItem->setSku($sku);
        $sourceItem->setQuantity((float)$qty);
        $sourceItem->setIsInStock($qty > 0);
        $sourceItem->setStatus(1);

        $this->_sourceItemsSaveInterface->execute([$sourceItem]);
    }
}
