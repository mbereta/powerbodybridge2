<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Import;

use \Magento\Catalog\Model\ProductFactory;
use \Magento\Catalog\Model\Product\Attribute\Source\Status;

class ProductDataComparator implements ProductDataComparatorInterface
{
    protected $productFactory;
    
    protected $logger;

    public function __construct(ProductFactory $productFactory
            
            ) {
        $this->productFactory = $productFactory;
    }

    public function compareResponseDataWithExisting(array $responseData) : array
    {
        $skuArray = array_keys($responseData);
        $productCollection = $this->productFactory->create()
            ->getCollection();
        $productCollection->addFieldToFilter('sku', ['in' => $skuArray])
            ->addFieldToSelect('import_updated_at')
            ->addFieldToSelect('status');

        if ($productCollection->getSize() == 0) {
            return $skuArray;
        }             

        foreach ($productCollection as $productModel) {
            $sku = $productModel->getData('sku');
            $productImportUpdatedAt = $productModel->getData('import_updated_at');
            $webserviceProductUpdatedAt = $responseData[$sku];
            $skuArrayPosition = array_search($sku, $skuArray);

            if ($productImportUpdatedAt >= $webserviceProductUpdatedAt) {
                unset($skuArray[$skuArrayPosition]);
            }

            if ((int)$productModel->getData('status') === Status::STATUS_DISABLED) {
                $skuArray[] = $productModel->getData('sku');
            }
        }
        
        return $skuArray;
    }
}
