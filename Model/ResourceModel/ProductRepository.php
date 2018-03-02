<?php

namespace Powerbody\Bridge\Model\ResourceModel;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class ProductRepository implements ProductRepositoryInterface
{
    private $productCollection;
    
    /** @var \Magento\Catalog\Model\Product */
    private $productModel;
    
    public function __construct(
        ProductCollection $productCollection,
        \Magento\Catalog\Model\Product $productModel
    ) {
        $this->productCollection = $productCollection;
        $this->productModel = $productModel;
    }
    
    public function getImportedProductsSkuArray()
    {
        return $this->productCollection
            ->addAttributeToSelect('sku')
            ->addAttributeToFilter('is_imported', true)
            ->addFieldToFilter('type_id', Type::TYPE_SIMPLE)
            ->getColumnValues('sku');
    }
    
    public function getIdBySku(string $sku) : int
    {
        return (int) $this->productModel->getIdBySku($sku);
    }
    
}
