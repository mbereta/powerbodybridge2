<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service\Sync\Entity;

use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute as EavEntityAttribute;
use Magento\Eav\Model\Entity\Attribute\Option as EavEntityAttributeOption;
use Powerbody\Bridge\Api\ClientInterface;
use Powerbody\Bridge\Entity\Attribute\Repository;

class Attribute
{
    private $attributeOptionManagementInterface;

    private $attributeOptionLabelInterface;

    private $attributeOptionValuesArray = [];

    private $client;

    private $eavConfig;

    private $eavEntityAttribute;

    private $eavEntityAttributeOption;

    private $mappedAttributesArray;

    private $repository;

    public function __construct(
        ClientInterface $client,
        EavConfig $eavConfig,
        EavEntityAttribute $eavEntityAttribute,
        EavEntityAttributeOption $eavEntityAttributeOption,
        AttributeOptionLabelInterface $attributeOptionLabelInterface,
        AttributeOptionManagementInterface $attributeOptionManagementInterface,
        Repository $repository
    ) {
        $this->attributeOptionLabelInterface = $attributeOptionLabelInterface;
        $this->attributeOptionManagementInterface = $attributeOptionManagementInterface;
        $this->client = $client;
        $this->eavConfig = $eavConfig;
        $this->eavEntityAttribute = $eavEntityAttribute;
        $this->eavEntityAttributeOption = $eavEntityAttributeOption;
        $this->repository = $repository;
    }

    public function updateAttributes()
    {
        $attributesArray = $this->repository->getAttributes();
        $this->mappedAttributesArray = $this->repository->getMappedAttributesArray();

        if (false === is_array($attributesArray)) {
            return [];
        }

        foreach ($attributesArray as $attributeCode => $attributesOptionsArray) {
            $this->processAttributeOptions(
                $this->mappedAttributesArray[$attributeCode],
                $attributesOptionsArray
            );
        }
    }

    public function saveAttributeOption(string $attributeCode, string $attributeOptionValue)
    {
        $this->attributeOptionLabelInterface->setStoreId(0);
        $this->attributeOptionLabelInterface->setLabel($attributeOptionValue);
        $this->eavEntityAttributeOption->setLabel($attributeOptionValue);
        $this->eavEntityAttributeOption->setStoreLabels([$this->attributeOptionLabelInterface]);
        $this->eavEntityAttributeOption->setSortOrder(0);
        $this->eavEntityAttributeOption->setIsDefault(false);
        $this->attributeOptionManagementInterface->add(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode,
            $this->eavEntityAttributeOption
        );
    }

    private function checkIfAttributeOptionExists(string $attributeId, string $attributeOptionValue) : bool
    {
        return isset($this->attributeOptionValuesArray[$attributeId][$attributeOptionValue]);
    }

    private function getAttributeOptionArray(string $attributeCode)
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $options = $attribute->getSource()->getAllOptions();   

        foreach ($options as $option) {
            if (false === empty(trim($option['label']))) {
                $this->attributeOptionValuesArray[$attribute->getId()][$option['label']] = $option['value'];
            }
        }
    }

    private function processAttributeOptions(string $attributeCode, array $attributesOptionsArray)
    {
        if (false === empty($attributesOptionsArray)) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $attribute = $this->getAttributeInfo(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);       
            $this->getAttributeOptionArray($attributeCode);

            foreach ($attributesOptionsArray as $attributeOption) {
                $this->processSingleAttributeOption($attribute, $attributeOption);
            }          
        }
    }

    private function getAttributeInfo(string $entityType, string $attributeCode) : EavEntityAttribute\AbstractAttribute
    {
        return $this->eavEntityAttribute->loadByCode($entityType, $attributeCode);
    }
    
    private function processSingleAttributeOption(\Magento\Eav\Model\Entity\Attribute $attribute, array $attributeOption)
    {
        if (true === isset($attributeOption['value']) && false === empty($attributeOption['value'])) {
            $attributeOptionValue = $attributeOption['label'];
            $option = $this->checkIfAttributeOptionExists($attribute->getId(), $attributeOptionValue);
            if (false === $option) {
                $this->saveAttributeOption($attribute['attribute_code'], $attributeOptionValue);
            }
        }   
    }
}
