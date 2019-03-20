<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Catalog\Model\Product\Attribute\Source\Status;
use \Powerbody\Bridge\Service\TaxService;

class SimpleProductImporter implements SimpleProductImporterInterface
{
    private $simpleProductUpdater;

    private $productFactory;

    private $productResourceModel;

    private $resourceConnection;

    private $dbConnection;

    private $productRepository;

    private $logger;

    private $storeManager;

    private $taxService;

    public function __construct(
        SimpleProductUpdater $simpleProductUpdater,
        ProductFactory $productFactory,
        ProductResourceModel $productResourceModel,
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        TaxService $taxService
    ) {
        $this->simpleProductUpdater = $simpleProductUpdater;
        $this->productFactory = $productFactory;
        $this->productResourceModel = $productResourceModel;
        $this->resourceConnection = $resourceConnection;
        $this->dbConnection =  $this->resourceConnection->getConnection();
        $this->productRepository =  $productRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->taxService = $taxService;
    }

    public function processImport(array $productsDataArray)
    {

        $productsCount = count($productsDataArray);
        $currentIndex = 1;

        foreach($productsDataArray as $productDataArray) {

            try {
                $this->dbConnection->beginTransaction();

                if (true === isset($productDataArray['rate'])) {
                    $productDataArray['tax_class_id'] = $this->taxService->getTaxClassIdByRate(floatval($productDataArray['rate']));
                }

                $productModel = $this->getProductModelBySku($productDataArray['sku']);
                $this->simpleProductUpdater->createOrUpdate($productModel, $productDataArray);
                $this->dbConnection->commit();

                $this->logger->info("Saved simple product: " . $currentIndex++ ." z ". $productsCount . " with id=" . $productModel->getId());
            } catch (\Exception $e) {
                $this->dbConnection->rollBack();
                $this->logger->debug($e->getMessage());
            }
        }
    }

    public function disableNotRequestedProducts(array $productSkuArray)
    {
        $storesArray = $this->storeManager->getStores(true);
        $productSkuArray = array_keys($productSkuArray);

        foreach ($storesArray as $storeModel) {

            $productCollection = $this->productFactory->create()
                ->setStoreId($storeModel->getId())
                ->getCollection();
            $productCollection->addFieldToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);

            if (false === empty($productSkuArray)) {
                $productCollection->addFieldToFilter('sku', ['nin' => $productSkuArray]);
                $productCollection->addFieldToFilter('is_imported', ['eq' => 1]);
            }

            $productsCount = $productCollection->getSize();
            $currentIndex = 1;

            $this->logger->info("Disable simples: ");

            foreach($productCollection as $productModel) {

                if ($productModel->getData('status') == Status::STATUS_ENABLED){
                    $productModel->setData('status', Status::STATUS_DISABLED);
                    $productModel->setData('website_ids', []);
                    $productModel->setStock('pb');
                    $this->logger->info("Disable product simple o sku: ". $productModel->getSku() . ' ' .$currentIndex++. ' from '. $productsCount);
                    $this->productRepository->save($productModel);
                }
            }
        }
    }

    private function getProductModelBySku(string $sku)
    {
        try {
            $productSkuModel = $this->productRepository->get($sku);
            $productModel = $this->productFactory->create();
            $this->productResourceModel->load(
                $productModel,
                $productSkuModel->getId()
            );
        } catch (\Exception $e) {
            return $this->productFactory->create();
        }

        return $productModel;
    }
}
