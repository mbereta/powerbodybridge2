<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service\Fixer;

use \Magento\Catalog\Model\Product;
use \Psr\Log\LoggerInterface;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Powerbody\Bridge\Entity\Product\SimpleProductRepository;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Model\ResourceModel\Product\Action;
use \Powerbody\Bridge\Service\TaxService;

class ProductTaxClass implements ProductTaxClassInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Store\Model\Store[]
     */
    private $stores;

    /**
     * @var SimpleProductRepository
     */
    protected $simpleProductRepository;

    /**
     * @var Action
     */
    private $productAction;

    /**
     * @var TaxService
     */
    private $taxService;

    public function __construct(
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        CollectionFactory $productCollectionFactory,
        SimpleProductRepository $simpleProductRepository,
        Action $productAction,
        TaxService $taxService
    ) {
        $this->logger = $logger;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stores = $storeManager->getStores();
        $this->simpleProductRepository = $simpleProductRepository;
        $this->productAction = $productAction;
        $this->taxService = $taxService;
    }

    public function fixProductsTaxClasses()
    {
        /* @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter(Product::TYPE_ID, \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $skus = $productCollection->getColumnValues('sku');
        $productsRates = $this->simpleProductRepository->getProductsTaxRates($skus);

        if (false === is_array($productsRates)) {
            return;
        }

        $dataToUpdate = [];

        foreach ($productCollection as $productModel) {
            if (true === array_key_exists($productModel->getData('sku'), $productsRates)) {
                $taxClassId = $this->taxService->getTaxClassIdByRate(floatval($productsRates[$productModel->getData('sku')]));
                $dataToUpdate[$taxClassId][] = $productModel->getId();
            }
        }

        $this->updateProductsTaxClasses($dataToUpdate);
    }

    private function updateProductsTaxClasses(array $dataToUpdate)
    {
        try {
            /* @var $store \Magento\Store\Model\Store */
            foreach ($this->stores as $store) {
                foreach ($dataToUpdate as $taxClassId => $productIdsToUpdate) {
                    $this->productAction->updateAttributes($productIdsToUpdate, ['tax_class_id' => $taxClassId], $store->getId());
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug($e);
        }
    }

}
