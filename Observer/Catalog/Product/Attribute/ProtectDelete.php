<?php

declare(strict_types=1);

namespace Powerbody\Bridge\Observer\Catalog\Product\Attribute;

class ProtectDelete implements \Magento\Framework\Event\ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer) : self
    {
        $attribute = $observer->getData('attribute');

        $importAttributes = \Powerbody\Bridge\Entity\Attribute\Repository::getImportedAttributes();

        if (true === in_array($attribute->getData('attribute_code'), $importAttributes)) {
            throw new \Exception('This attribute was imported and can\'t be deleted');
        }

        return $this;
    }

}
