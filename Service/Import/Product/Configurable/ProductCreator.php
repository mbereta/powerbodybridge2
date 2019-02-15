<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import\Product\Configurable;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionsFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Powerbody\Bridge\Service\ArrayUniquenessProviderInterface;
use Powerbody\Manufacturer\Model\ResourceModel\ManufacturerRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use \Powerbody\Manufacturer\Model\Manufacturer;
use \Powerbody\Manufacturer\Model\ResourceModel\Manufacturer as ManufacturerResourceModel;
use Powerbody\Bridge\Service\Import\IdTranslatorInterface;
use Powerbody\Bridge\Entity\Attribute\Repository as AttributeRepository;
use Powerbody\Manufacturer\Service\Manufacturer\ProductServiceInterface;

class ProductCreator implements ProductCreatorInterface
{
    private $productFactory;

    private $optionsFactory;

    private $attributeRepository;

    private $productAttributeRepository;

    private $productRepository;

    private $manufacturerRepository;

    private $arrayUniquenessProvider;

    private $urlPersist;

    private $urlRewriteGenerator;

    private $categoryIdTranslator;

    private $manufacturerIdTranslator;

    private $manufacturerResourceModel;

    private $productService;

    private $attributes = [];

    public function __construct(
        ProductFactory $productFactory,
        OptionsFactory $optionsFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductRepositoryInterface $productRepository,
        ArrayUniquenessProviderInterface $arrayUniquenessProvider,
        UrlPersistInterface $urlPersist,
        ProductUrlRewriteGenerator $urlRewriteGenerator,
        IdTranslatorInterface $categoryIdTranslator,
        IdTranslatorInterface $manufacturerIdTranslator,
        ManufacturerRepositoryInterface $manufacturerRepository,
        AttributeRepository $attributeRepository,
        ManufacturerResourceModel $manufacturerResourceModel,
       // ProductServiceInterface $productService,
        array $attributes
    ) {
        $this->productFactory = $productFactory;
        $this->optionsFactory = $optionsFactory;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->arrayUniquenessProvider = $arrayUniquenessProvider;
        $this->urlPersist = $urlPersist;
        $this->urlRewriteGenerator = $urlRewriteGenerator;
        $this->categoryIdTranslator = $categoryIdTranslator;
        $this->manufacturerIdTranslator = $manufacturerIdTranslator;
        $this->manufacturerResourceModel = $manufacturerResourceModel;
       // $this->productService = $productService;
        $this->attributes = $attributes;
    }

    public function create(array $dataArray)
    {
        $sku = $dataArray['sku'];
        $product = $this->getConfigurableProductInstance($sku);
        $childProducts = [];

        if (true === isset($dataArray['children'])) {
            $childProducts = $this->createChildrenProductsArray($dataArray['children']);
        }

        if (empty($childProducts)) {
            return;
        }

        $attributeValues = $this->buildAttributeValuesArray($childProducts);
        $configurableAttributesData = $this->buildConfigurableAttributesArray($attributeValues);

        $extensionConfigurableAttributes = $product->getExtensionAttributes();
        $extensionConfigurableAttributes->setData(
            'configurable_product_options',
            $this->optionsFactory->create($configurableAttributesData)
        );
        $extensionConfigurableAttributes->setData(
            'configurable_product_links',
            $this->getIdsOfProductsInArray($childProducts)
        );

        $taxClassId = $this->getTaxClass($childProducts);

        $product->setExtensionAttributes($extensionConfigurableAttributes);
        $product->setName($dataArray['name']);
        $product->setSku($sku);
        $product->setTaxClassId($taxClassId);
        $product->setAttributeSetId($product->getDefaultAttributeSetId());
        $product->setWebsiteIds([1]);
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product->setStatus(Status::STATUS_ENABLED);
        $product->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
        $manufacturerArray = $this->manufacturerIdTranslator->translateBaseToClientIds($dataArray['manufacturers']);

        if (false === empty($manufacturerArray)) {
            $product->setData('manufacturers', $manufacturerArray);
            $this->processManufacturerAttribute($product, $manufacturerArray);
        }

        $product->setData('category_ids', $this->categoryIdTranslator->translateBaseToClientIds($dataArray['categories']));
        $product->setData('import_updated_at', $dataArray['updated_at']);
        $product->setData('is_imported', true);
        $product->setUrlKey($product->formatUrlKey($sku . '-' . $dataArray['name']));

        $this->setProductsDescriptions($product, $childProducts);
        $this->productRepository->save($product);

        if (false === empty($manufacturerArray)) {
            $productModel = $this->productRepository->get($product->getData('sku'));
            foreach($manufacturerArray as $manufacturerId) {
                $this->productService->createManufacturerProduct($manufacturerId, (int)$productModel->getId());
            }
        }

        $this->setChildrenAsNotVisibleIndividually($childProducts);
        $this->copyChildrenMediaGalleryToConfigurable($childProducts, $product);
    }

    private function getTaxClass(array $childProducts) : int
    {
        $taxClass = 0;

        foreach ($childProducts as $child) {
            $taxClass = $child->getData('tax_class_id');
        }

        return (int) $taxClass;
    }

    private function createChildrenProductsArray(array $childrenArray) : array
    {
        $childProducts = [];
        $mappedAttributes = $this->attributeRepository->getMappedAttributesArray();

        foreach ($childrenArray as $childSku) {
            try {
                $childProductModel = $this->productRepository->get($childSku, false, null, true);
                $attributeExists = false;
                foreach ($mappedAttributes as $attributeCode) {
                    if ($childProductModel->getData($attributeCode) != null) {
                        $attributeExists = true;
                    }
                }
                if (true === $attributeExists) {
                    $childProducts[] = $childProductModel;
                }
            } catch (\Exception $e) {}
            }

        return $childProducts;
    }

    private function buildAttributeValuesArray(array $childProducts) : array
    {
        $attributeValues = [];
        foreach ($childProducts as $childProduct) {
            foreach ($this->attributes as $code) {
                $attributeValue = $childProduct->getData($code);
                $productAttribute = $this->productAttributeRepository->get($code);
                $options = $productAttribute->getOptions();
                foreach ($options as $option) {
                    if ($option->getValue() == $attributeValue && !empty($option->getValue())) {
                        $attributeValues[$code][] = [
                            'label' => $option->getLabel(),
                            'attribute_id' => $productAttribute->getAttributeId(),
                            'value_index' => $option->getValue(),
                        ];
                    }
                }
            }
        }

        return $attributeValues;
    }

    private function buildConfigurableAttributesArray(array $attributeValues) : array
    {
        $configurableAttributesData = [];
        $position = 1;
        foreach ($attributeValues as $code => $attributeValuesArray) {
            $attribute = $this->productAttributeRepository->get($code);
            $configurableAttributesData[] = [
                'attribute_id' => $attribute->getAttributeId(),
                'code' => $code,
                'label' => $attribute->getDefaultFrontendLabel(),
                'position' => $position++,
                'values' => $this->arrayUniquenessProvider->arrayUniqueMultidimensional($attributeValuesArray),
            ];
        }

        return $configurableAttributesData;
    }

    private function getIdsOfProductsInArray(array $childProducts) : array
    {
        return array_map(function (Product $product) {
            return $product->getId();
        }, $childProducts);
    }

    private function getConfigurableProductInstance($sku) : Product
    {
        /** @var Product $product */
        $product = $this->productFactory->create();
        $idBySku = $product->getResource()->getIdBySku($sku);
        if ($idBySku !== false) {
            $product->getResource()->load($product, $idBySku);
        }
        $product->setTypeId(Configurable::TYPE_CODE);

        return $product;
    }

    private function setChildrenAsNotVisibleIndividually(array $childrenArray)
    {
        foreach ($childrenArray as $childProduct) {
            if ($childProduct instanceof Product && $childProduct->getTypeInstance() instanceof Simple) {
                $childProduct->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
                $childProduct->getResource()->saveAttribute($childProduct,'visibility');
            }
        }
    }

    private function copyChildrenMediaGalleryToConfigurable(array $children, Product $product)
    {
        $product->setMediaGalleryEntries([]);
        $this->productRepository->save($product);

        $galleryEntries = [];

        foreach ($children as $child) {
            /** @var Product $child */
            $galleryEntries = array_merge($galleryEntries, $child->getMediaGalleryImages()->getItems());
        }

        /** @var \Magento\Framework\DataObject[] $galleryEntries */
        $files = array_map(function (\Magento\Framework\DataObject $entry) {
            return $entry->getData('path');
        }, $galleryEntries);

        $files = array_unique($files);
        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            $product->addImageToMediaGallery($file, ['image','thumbnail','small_image'], false, false);
        }

        $product->getResource()->save($product);
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

    /**
     * @param Product $product
     * @param Product[] $childProducts
     */
    private function setProductsDescriptions(Product $product, array $childProducts)
    {
        foreach ($childProducts as $child) {
            $description = $child->getData('description');
            $shortDescription = $child->getData('short_description');

            if (null !== $description
                && false === empty($description)
                && $description !== '&nbsp;'
            ) {
                $product->setData('description', $description);
            }

            if (null !== $shortDescription
                && false === empty($shortDescription)
                && $shortDescription !== '&nbsp;'
            ) {
                $product->setData('short_description', strip_tags($shortDescription));
            }
        }
    }

}
