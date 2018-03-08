<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Observer\Catalog\Product;

class ProtectDelete implements \Magento\Framework\Event\ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer) : self
    {
        $product = $observer->getData('product');

        $isImported = $product->getResource()->getAttributeRawValue($product->getId(),'is_imported', 0);

        if (true === boolval($isImported)) {
            throw new \Powerbody\Bridge\Exception\ImportedProductDeleteException('This product is imported and can\'t be deleted');
        }

        return $this;
    }

}
