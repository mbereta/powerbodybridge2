<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Cron;

use Powerbody\Bridge\Entity\Product\PriceProductRepositoryInterface;
use Powerbody\Bridge\Service\Import\Product\PriceUpdaterInterface;
use Powerbody\Bridge\Model\ResourceModel\ProductRepositoryInterface;
use Powerbody\Bridge\System\Configuration\ConfigurationReaderInterface;
use \Psr\Log\LoggerInterface as Logger;

class PriceData
{
    private $priceProductRepository;

    private $priceUpdater;

    private $productRepository;

    private $logger;

    private $configurationReader;

    public function __construct(
        PriceProductRepositoryInterface $priceProductRepository,
        PriceUpdaterInterface $priceUpdater,
        ProductRepositoryInterface $productRepository,
        Logger $logger,
        ConfigurationReaderInterface $configurationReader
    ) {
        $this->priceProductRepository = $priceProductRepository;
        $this->priceUpdater = $priceUpdater;
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
        $priceDataArray = $this->priceProductRepository->getProductPriceDataForSkuArray($productSkuArray);

        if (true === empty($priceDataArray)) {
            return;
        }

        $this->priceUpdater->processImport($priceDataArray);
    }
}
