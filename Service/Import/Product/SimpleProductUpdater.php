<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Powerbody\Bridge\Model\Imported\CategoryFactory as ImportedCategoryFactory;
use Powerbody\Bridge\Model\Imported\ManufacturerFactory as ImportedManufacturerFactory;
use Powerbody\Bridge\Entity\Attribute\Repository as AttributeRepository;
use Powerbody\Bridge\Model\ResourceModel\Imported\ManufacturerRepositoryInterface as ImportedManufacturerRepositoryInterface;
use Powerbody\Bridge\Service\ImageFileNotFoundException;
use Powerbody\Manufacturer\Model\ResourceModel\ManufacturerRepositoryInterface;
use Powerbody\Bridge\Service\Sync\Entity\Attribute as AttributeService;
use Powerbody\Manufacturer\Service\Manufacturer\ProductServiceInterface;
use Powerbody\Bridge\Service\ImageDownloaderInterface;
use \Powerbody\Manufacturer\Model\Manufacturer;
use \Powerbody\Manufacturer\Model\ResourceModel\Manufacturer as ManufacturerResourceModel;
use \Magento\Catalog\Model\Product\Visibility;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use Powerbody\Bridge\Service\Import\IdTranslatorInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;

class SimpleProductUpdater implements SimpleProductUpdaterInterface
{
    const PRODUCT_ATTRIBUTE_SET_ID = 4;

    const ADMIN_STORE_ID = 0;

    private $importedCategoryFactory;

    private $importedManufacturerFactory;

    private $attributeRepository;

    private $productResourceModel;

    private $stockRegistry;

    private $importedManufacturerRepository;

    private $manufacturerRepository;

    private $attributeService;

    private $productService;

    private $storeManager;

    private $imageDownloader;

    private $productRepository;

    private $productFactory;

    private $manufacturerResourceModel;

    private $manufacturerIdTranslator;

    private $categoryIdTranslator;

    private $urlPersist;

    private $urlRewriteGenerator;

    public function __construct(
        ImportedCategoryFactory $importedCategoryFactory,
        ImportedManufacturerFactory $importedManufacturerFactory,
        AttributeRepository $attributeRepository,
        ProductResourceModel $productResourceModel,
        StockRegistryInterface $stockRegistry,
        ImportedManufacturerRepositoryInterface $importedManufacturerRepository,
        ManufacturerRepositoryInterface $manufacturerRepository,
        AttributeService $attributeService,
       // ProductServiceInterface $productService,
        StoreManagerInterface $storeManager,
        ImageDownloaderInterface $imageDownloader,
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory,
        ManufacturerResourceModel $manufacturerResourceModel,
        IdTranslatorInterface $categoryIdTranslator,
        IdTranslatorInterface $manufacturerIdTranslator,
        UrlPersistInterface $urlPersist,
        ProductUrlRewriteGenerator $urlRewriteGenerator
    ) {
        $this->importedCategoryFactory = $importedCategoryFactory;
        $this->importedManufacturerFactory = $importedManufacturerFactory;
        $this->attributeRepository = $attributeRepository;
        $this->productResourceModel = $productResourceModel;
        $this->stockRegistry = $stockRegistry;
        $this->importedManufacturerRepository = $importedManufacturerRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->attributeService = $attributeService;
     //   $this->productService = $productService;
        $this->storeManager = $storeManager;
        $this->imageDownloader = $imageDownloader;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->manufacturerResourceModel = $manufacturerResourceModel;
        $this->categoryIdTranslator = $categoryIdTranslator;
        $this->manufacturerIdTranslator = $manufacturerIdTranslator;
        $this->urlPersist = $urlPersist;
        $this->urlRewriteGenerator = $urlRewriteGenerator;
    }

    public function createOrUpdate(Product $productModel, array $productDataArray)
    {
        $productModel->setData('is_saving_by_import', true);

        $sku = $productDataArray['sku'];
        $isUpdatedWhileImport = boolval($productModel->getData('is_updated_while_import'));
        $isNew = !boolval($productModel->getId());
        $productDataArray['category_ids'] = $this->categoryIdTranslator
            ->translateBaseToClientIds($productDataArray['category_ids']);
        $productDataArray['manufacturers'] = $this->manufacturerIdTranslator
            ->translateBaseToClientIds($productDataArray['manufacturers']);
        $productDataArray['import_updated_at'] = $productDataArray['updated_at'];
        $attributeDataArray = $this
            ->translateAttributeDataToBaseAttributeValue($productDataArray['attributes_source']);
        $stockDataArray = $this->getStockDataArray($productDataArray);
        $productDataArray = $this->removeNotUsedDataFromArray($productDataArray);
        $productDataArray['base_price'] = $productDataArray['price'];

        if (true === isset($productDataArray['description'])) {
            $productDataArray['description'] = $this->closeHtmlTags((string) $productDataArray['description']);
        }

        if (true === isset($productDataArray['short_description'])) {
            $productDataArray['short_description'] = $this->closeHtmlTags((string) $productDataArray['short_description']);
        }

        if (true === isset($productDataArray['meta_description'])) {
            $productDataArray['meta_description'] = strip_tags((string) $productDataArray['meta_description']);
        }

        if (false === $isNew) {
            unset($productDataArray['price']);
            if (false === $isUpdatedWhileImport) {
                $productDataArray = $this->removeNotUpdatedDataFromArray($productDataArray);
            }
        }

        $productModel
            ->addData($productDataArray)
            ->addData($attributeDataArray)
            ->addData([
                'is_imported' => true,
                'visibility' => Visibility::VISIBILITY_BOTH,
                'attribute_set_id' => $this->getProductAttributeSetId(),
                'website_ids' => $this->getImportProductWebsiteIds(),
                'status' => (int)$productDataArray['status'],
            ]);
        
        if (false === @getimagesize($productModel->getData('image_url'))) {
            $productModel->addData(['image_url' => null]);
        }

        if (true === $isNew) {
            $productModel->addData(['is_updated_while_import' => true]);
        }

        $this->processManufacturerAttribute($productModel, $productDataArray['manufacturers']);

        if (true === $isNew || true === $isUpdatedWhileImport) {
            $this->downloadImageForProduct($productModel);
        }

        $productModel->setUrlKey($productModel->formatUrlKey($sku . '-' . $productDataArray['name']));

        $imageAttributes = [
            'image' => $productModel->getData('image'),
            'small_image' => $productModel->getData('small_image'),
            'thumbnail' => $productModel->getData('thumbnail'),
        ];

        $this->productResourceModel->save($productModel);

        $this->updateStockItemForProductBySku($sku, $stockDataArray);
        $this->updateManufacturerDataForProduct($productModel, $productDataArray['manufacturers']);
        $this->updateAdminStoreView($productModel, $imageAttributes);
    }

    private function updateAdminStoreView(Product $productModel, array $imageAttributes)
    {
        $productModel->setStoreId(self::ADMIN_STORE_ID);
        $productModel->addData($imageAttributes);
        $this->productResourceModel->save($productModel);
    }

    private function processManufacturerAttribute(Product $productModel, array $manufacturerArray)
    {
        $manufacturerId = reset($manufacturerArray);
        $manufacturerModel = $this->manufacturerRepository
            ->getManufacturerById($manufacturerId);

        if (null !== $manufacturerModel->getData('attribute_option_id')) {
            $attributeOptionId = $manufacturerModel->getData('attribute_option_id');
        } else {
            $manufacturerName = trim($manufacturerModel->getData('name'));
            $attributeOptionId = $this->attributeRepository
                ->getAttributeOptionIdByAttributeCodeAndValue('manufacturer', $manufacturerName);
        }

        if ('' === $attributeOptionId) {
            $attributeOptionId = $this->createManufacturerAttributeOption('manufacturer', $manufacturerName);
        }

        if ($manufacturerModel->getData('attribute_option_id') !== $attributeOptionId) {
            $this->updateManufacturerData($manufacturerModel, $attributeOptionId);
        }

        $productModel->addData(['manufacturer' => $attributeOptionId]);
    }

    private function updateManufacturerData(Manufacturer $manufacturerModel, $attributeOptionId)
    {
        $manufacturerModel->addData(['attribute_option_id' => $attributeOptionId]);
        $this->manufacturerResourceModel->save($manufacturerModel);
    }
    
    private function updateManufacturerDataForProduct(Product $productModel, array $manufacturerArray)
    {
        foreach($manufacturerArray as $manufacturerId) {
            $this->productService->createManufacturerProduct($manufacturerId, (int)$productModel->getId());
        }
    }

    private function createManufacturerAttributeOption(string $attributeCode, string $manufacturerName) : string
    {
        $this->attributeService->saveAttributeOption($attributeCode, $manufacturerName);

        return $this->attributeRepository
            ->getAttributeOptionIdByAttributeCodeAndValue('manufacturer', $manufacturerName);
    }

    private function translateAttributeDataToBaseAttributeValue(array $attributeSourceArray) : array
    {
        $attributesArray = [];
        $fullAttributeArray = $this->attributeRepository->getAttributesWithOptionsArray();
        $attributeMapArray = $this->attributeRepository->getMappedAttributesArray();
        foreach ($attributeSourceArray as $attributeCode => $attributeName) {
            if (true === isset($fullAttributeArray[$attributeCode])) {
                $attributeArrayForCode = $fullAttributeArray[$attributeCode];
                $arrayKey = array_search($attributeName, array_column($attributeArrayForCode, 'label'));
                $attributesArray[$attributeMapArray[$attributeCode]] = $attributeArrayForCode[$arrayKey]['value'];
            }
        }

        return $attributesArray;
    }

    private function removeNotUsedDataFromArray(array $productDataArray) : array
    {
        unset(
            $productDataArray['entity_id'],
            $productDataArray['stock_item'],
            $productDataArray['website_ids'],
            $productDataArray['image'],
            $productDataArray['small_image'],
            $productDataArray['thumbnail'],
            $productDataArray['qty'],
            $productDataArray['stock_status']
        );

        return $productDataArray;
    }

    private function removeNotUpdatedDataFromArray(array $productDataArray) : array
    {
        unset(
            $productDataArray['name'],
            $productDataArray['meta_title'],
            $productDataArray['meta_description'],
            $productDataArray['price'],
            $productDataArray['description'],
            $productDataArray['short_description'],
            $productDataArray['image_url']
        );

        return $productDataArray;
    }

    private function getImportProductWebsiteIds() : array
    {
        $websites = $this->storeManager->getWebsites(true);
        $websitesArray = [];

        foreach ($websites as $website) {
            $websitesArray[] = $website->getData('website_id');
        }

        return $websitesArray;
    }

    private function getProductAttributeSetId() : int
    {
        return self::PRODUCT_ATTRIBUTE_SET_ID;
    }

    private function downloadImageForProduct(Product $productModel)
    {
        try {
            $imageUrl = $productModel->getData('image_url');

            if (null !== $productModel->getId()) {
                $this->deleteProductMediaGalleryEntries($productModel);
            }

            if (null !== $imageUrl) {
                $fileName = explode('/', $imageUrl);
                $fileName = end($fileName);
                $this->imageDownloader->downloadImage($imageUrl, BP. '/pub/media/import/', (string) $fileName);

                $productModel->addImageToMediaGallery(
                    BP. '/pub/media/import/' . (string)  $fileName,
                    ['image', 'small_image', 'thumbnail'],
                    false,
                    false
                );
            }
        } catch (\Exception $ex) {
        } catch (ImageFileNotFoundException $e) {
        }
    }

    private function deleteProductMediaGalleryEntries(Product $productModel)
    {
        $productModel->setMediaGalleryEntries([]);
        $this->productRepository->save($productModel);
    }

    private function getStockDataArray(array $productDataArray) : array
    {
        return [
            'use_config_manage_stock' => 0,
            'manage_stock'            => 1,
            'is_in_stock'             => $productDataArray['stock_status'],
            'qty'                     => $productDataArray['qty']
        ];
    }

    private function updateStockItemForProductBySku($sku, array $stockDataArray)
    {
        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        $stockItem->setQty($stockDataArray['qty']);
        $stockItem->setIsInStock($stockDataArray['qty'] > 0);
        $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
    }

    private function closeHtmlTags(string $rawInput) : string
    {
        return $rawInput;
        $tidy = new \tidy();

        $input = '<body>' . $rawInput . '</body>';
        $output = $tidy->repairString($input, [
            'output-xml' => true,
            'input-xml' => true,
            'wrap' => false,
        ]);

        return substr($output, 6, -7);
    }
}
