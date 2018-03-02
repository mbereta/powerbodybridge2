<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Cron;

use Powerbody\Bridge\Entity\Product\PriceProductRepositoryInterface;
use Powerbody\Bridge\Service\Import\Product\PriceUpdaterInterface;
use Powerbody\Bridge\Model\ResourceModel\ProductRepositoryInterface;
use \Psr\Log\LoggerInterface as Logger;

class PriceData
{
    private $priceProductRepository;
    private $priceUpdater;
    private $productRepository;
    private $logger;

    public function __construct(
        PriceProductRepositoryInterface $priceProductRepository,
        PriceUpdaterInterface $priceUpdater,
        ProductRepositoryInterface $productRepository,
        Logger $logger
    ) {
        $this->priceProductRepository = $priceProductRepository;
        $this->priceUpdater = $priceUpdater;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function run()
    {
        $this->logger->debug(__('Started price import:') . date('Y-m-d H:i:s', time()));

        $productSkuArray = $this->productRepository->getImportedProductsSkuArray();
        $priceDataArray = $this->priceProductRepository->getProductPriceDataForSkuArray($productSkuArray);

        if (false === empty($priceDataArray)) {
            $this->priceUpdater->processImport($priceDataArray);
        }

        $this->logger->debug(__('Ended price import:') . date('Y-m-d H:i:s', time()));
    }
}
