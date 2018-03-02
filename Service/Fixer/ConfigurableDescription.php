<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Service\Fixer;

use Magento\Catalog\Model\Product;

class ConfigurableDescription implements ConfigurableDescriptionInterface
{
    
    /** @var \Psr\Log\LoggerInterface */
    private $logger;
    
    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    private $productCollectionFactory;
    
    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute */
    private $eavAttribute;
    
    /** @var \Magento\Store\Model\Store[] */
    private $stores;
    
    /** @var \Powerbody\Bridge\Model\ResourceModel\Product\Attribute\TextValueRepository */
    private $textValueRepository;
    
    private $descriptionAttributeId;
    
    private $shortDescriptionAttributeId;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Powerbody\Bridge\Model\ResourceModel\Product\Attribute\TextValueRepository $textValueRepository
    ) {
        $this->logger = $logger;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavAttribute = $eavAttribute;
        $this->textValueRepository = $textValueRepository;
    
        $this->stores = $storeManager->getStores();
    
        $this->descriptionAttributeId = (int) $this->eavAttribute->getIdByCode(
            \Magento\Catalog\Model\Product::ENTITY,
            \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_DESCRIPTION
        );
        $this->shortDescriptionAttributeId = (int) $this->eavAttribute->getIdByCode(
            \Magento\Catalog\Model\Product::ENTITY,
            \Magento\Catalog\Api\Data\ProductAttributeInterface::CODE_SHORT_DESCRIPTION
        );
    }
    
    public function fixDescriptions()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter(Product::TYPE_ID, \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        
        $products = $productCollection->load();
        
        foreach ($products as $product) {
            $this->fixDescriptionForProduct($product);
        }
    }
    
    private function fixDescriptionForProduct(Product $product)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $childProducts */
        $childProducts = $product->getTypeInstance()
            ->getUsedProductCollection($product)
            ->addAttributeToSelect($this->descriptionAttributeId)
            ->addAttributeToSelect($this->shortDescriptionAttributeId);
    
        $descriptionData = [
            'description' => $this->findBestDescription($product, $childProducts, 'description'),
            'short_description' => $this->findBestDescription($product, $childProducts, 'short_description'),
        ];
    
        $this->saveProduct($product, $descriptionData);
    }
    
    private function saveProduct(Product $product, array $descriptionData)
    {
        $productId = (int) $product->getId();
        
        /** @var \Magento\Store\Model\Store $store */
        foreach ($this->stores as $store) {
            $storeId = (int) $store->getId();
            
            try {
                $textValue = $this->textValueRepository->getInstance($storeId, $productId, $this->descriptionAttributeId);
                $textValue->addData([
                    'attribute_id' => $this->descriptionAttributeId,
                    'store_id' => $storeId,
                    'entity_id' => $productId,
                    'value' => $descriptionData['description'],
                ]);
                $this->textValueRepository->save($textValue);
    
                $textValue = $this->textValueRepository->getInstance($storeId, $productId, $this->shortDescriptionAttributeId);
                $textValue->addData([
                    'attribute_id' => $this->shortDescriptionAttributeId,
                    'store_id' => $storeId,
                    'entity_id' => $productId,
                    'value' => $descriptionData['short_description'],
                ]);
                $this->textValueRepository->save($textValue);
            } catch (\Exception $e) {
                $this->logger->debug($e);
            }
        }
    }
    
    private function findBestDescription(
        Product $product,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $childProducts,
        string $field
    ) : string {
        $bestValue = (string) $product->getData($field);
        $bestLength = strlen(strip_tags($bestValue));
        
        foreach ($childProducts as $child) {
            $value = (string) $child->getData($field);
            $length = strlen(strip_tags($value));
    
            if ($length > $bestLength) {
                $bestValue = $value;
                $bestLength = $length;
            }
        }
        
        return $bestValue;
    }
    
}
