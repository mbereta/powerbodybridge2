<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product;

use \Magento\Catalog\Api\ProductRepositoryInterface;
use Powerbody\Manufacturer\Service\Manufacturer\MarginServiceInterface;
use Psr\Log\LoggerInterface;
use \Magento\Catalog\Model\ResourceModel\Product;

class PriceUpdater implements PriceUpdaterInterface
{
    private $productRepository;
    private $logger;
    private $productResourceModel;
    private $marginService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        Product $productResourceModel,
        MarginServiceInterface $marginService
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->productResourceModel = $productResourceModel;
        $this->marginService = $marginService;
    }

    public function processImport(array $priceDataArray)
    {
        try {
            foreach ($priceDataArray as $priceData) {
                /* @var $productModel \Magento\Catalog\Model\Product */
                $productModel = $this->productRepository->get($priceData['sku']);
                $this->updateBasePrice($productModel, $priceData);
                $this->updatePrice($productModel);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    private function updateBasePrice(\Magento\Catalog\Model\Product $productModel, array $priceData)
    {
        $basePriceRounded = round($productModel->getData('base_price'), 2);
        $importedPriceRounded = round($priceData['price'], 2);
        if ($importedPriceRounded !== $basePriceRounded) {
            $productModel->setData('base_price', $priceData['price']);
            $this->productResourceModel->saveAttribute($productModel, 'base_price');
        }
    }

    private function updatePrice(\Magento\Catalog\Model\Product $product)
    {
        $priceIncludingMargin = $this->marginService->getPriceIncludingMargin($product);
        $priceRounded = round($product->getData('price'), 2);
        if ($priceRounded !== $priceIncludingMargin) {
            $product->setPrice($priceIncludingMargin);
            $this->productResourceModel->saveAttribute($product, 'price');
        }
    }
}
