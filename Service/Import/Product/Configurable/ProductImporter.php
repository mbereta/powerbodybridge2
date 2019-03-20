<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product\Configurable;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use \Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use \Psr\Log\LoggerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\App\ResourceConnection;

class ProductImporter implements ProductImporterInterface
{
    private $productCreator;

    private $logger;

    private $productFactory;

    private $productRepository;

    private $resourceConnection;

    private $dbConnection;

    private $urlPersist;


    public function __construct(
        ProductCreatorInterface $productCreator,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        UrlPersistInterface $urlPersist,
        ResourceConnection $resourceConnection
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->productCreator = $productCreator;
        $this->logger = $logger;
        $this->urlPersist = $urlPersist;
        $this->resourceConnection = $resourceConnection;
        $this->dbConnection = $this->resourceConnection->getConnection();
    }

    public function processImport(array $productsDataArray)
    {
        $productsCount = count($productsDataArray);
        $currentIndex=1;


        foreach($productsDataArray as $productDataArray) {
            try {
                $this->dbConnection->beginTransaction();
                if(empty($productDataArray['stock'])){
                    $productDataArray['stock']='pb';
                }
                $this->productCreator->create($productDataArray);

                $this->dbConnection->commit();
                $this->logger->info("Saved configurable product: " . $currentIndex++ ." from ". $productsCount . " " . $productDataArray['entity_id']);
            } catch (\Exception $e) {
                $this->dbConnection->rollBack();
                $this->logger->info("Cannot saved configurable product: " . $currentIndex++ ." from ". $productsCount . " " . $productDataArray['entity_id']);
                $this->logger->debug(__('Transaction for product has been rolled back') . ': ' . $productDataArray['sku']);
                $this->logger->debug($e->getMessage(), ['trace' => $e->getTraceAsString(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            }
        }
    }

    public function disableNotRequestedProducts(array $productSkuArray)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productFactory->create()->getCollection();
        $productCollection->addFieldToFilter('type_id', Configurable::TYPE_CODE);
        $productSkuArray = array_keys($productSkuArray);

        if (false === empty($productSkuArray)) {
            $productCollection->addFieldToFilter('sku', ['nin' => $productSkuArray]);
            $productCollection->addFieldToFilter('is_imported',['eq'=>1]);
        }

        $productsCount = $productCollection->getSize();
        $currentIndex = 1;

        $this->logger->info("Disabling configurable products: ");

        foreach($productCollection as $productModel) {
            /* @var \Magento\Catalog\Model\Product $productModel */

            if($productModel->getData('status') == Status::STATUS_ENABLED) {
                $productModel->setData('status', Status::STATUS_DISABLED);
                $productModel->setData('website_ids', []);
                $this->productRepository->save($productModel);
                $productModel->setStoreId(0);
                $this->logger->info("Disable configurable product: " . $currentIndex++ . " from " . $productsCount . "  with id=" . $productModel->getId());
                $this->productRepository->save($productModel);
                $this->urlPersist->deleteByData([UrlRewrite::ENTITY_ID => $productModel->getId()]);
            }
        }
    }
}
