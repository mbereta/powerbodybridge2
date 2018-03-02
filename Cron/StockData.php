<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Cron;

use Powerbody\Bridge\Entity\Product\StockProductRepositoryInterface;
use Powerbody\Bridge\Service\Import\Product\StockUpdaterInterface;
use Powerbody\Bridge\Model\ResourceModel\ProductRepositoryInterface;
use \Psr\Log\LoggerInterface as Logger;

class StockData
{
    private $stockProductRepository;

    private $stockUpdater;

    private $productRepository;

    private $logger;

    public function __construct(
        StockProductRepositoryInterface $stockProductRepository,
        StockUpdaterInterface $stockUpdater,
        ProductRepositoryInterface $productRepository,
        Logger $logger
    ) {
        $this->stockProductRepository = $stockProductRepository;
        $this->stockUpdater = $stockUpdater;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function run()
    {
        $this->logger->debug(__('Started stock import:') . date('Y-m-d H:i:s', time()));

        $productSkuArray = $this->productRepository->getImportedProductsSkuArray();

        $stockDataArray = $this->stockProductRepository->getProductStockDataForSkuArray($productSkuArray);

        if (true === empty($stockDataArray)) {
            return;
        }

        $this->stockUpdater->processImport($stockDataArray);

        $this->logger->debug(__('Ended stock import:') . date('Y-m-d H:i:s', time()));

        return;
    }
}
