<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Cron;

use Powerbody\Bridge\Entity\Product\StockProductRepositoryInterface;
use Powerbody\Bridge\Service\Import\Product\StockUpdaterInterface;
use Powerbody\Bridge\Model\ResourceModel\ProductRepositoryInterface;
use Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface;
use \Psr\Log\LoggerInterface as Logger;

class StockData
{
    private $stockProductRepository;

    private $stockUpdater;

    private $productRepository;

    private $logger;

    private $configurationReader;

    public function __construct(
        StockProductRepositoryInterface $stockProductRepository,
        StockUpdaterInterface $stockUpdater,
        ProductRepositoryInterface $productRepository,
        Logger $logger,
        ConfigurationReaderInterface $configurationReader
    ) {
        $this->stockProductRepository = $stockProductRepository;
        $this->stockUpdater = $stockUpdater;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->configurationReader = $configurationReader;
    }

    public function run()
    {
        if (false === $this->configurationReader->getIsEnabled()) {
            return $this;
        }

        $productSkuArray = $this->productRepository->getImportedProductsSkuArray();

        $stockDataArray = $this->stockProductRepository->getProductStockDataForSkuArray($productSkuArray);

        if (true === empty($stockDataArray)) {
            return;
        }

        $this->stockUpdater->processImport($stockDataArray);
    }
}
