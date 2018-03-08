<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Attribute;

use Powerbody\Bridge\Api\ClientInterface;
use \Magento\Eav\Model\Config as EavConfig;
use \Magento\Eav\Setup\EavSetupFactory;

class Repository implements RepositoryInterface
{
    const DROPCLIENT_COLOR_ATTRIBUTE_CODE = 'powerbody_color';
    const DROPCLIENT_FLAVOUR_ATTRIBUTE_CODE = 'powerbody_flavour';
    const DROPCLIENT_SIZE_ATTRIBUTE_CODE = 'powerbody_size';
    const DROPCLIENT_WEIGHT_ATTRIBUTE_CODE = 'powerbody_weight';
    const DROPCLIENT_MIXED_WEIGHT_ATTRIBUTE_CODE = 'powerbody_mixed_weight';
    
    const POWERBODY_COLOR_ATTRIBUTE_CODE = 'color';
    const POWERBODY_FLAVOUR_ATTRIBUTE_CODE = 'flavour';
    const POWERBODY_SIZE_ATTRIBUTE_CODE = 'size';
    const POWERBODY_WEIGHT_ATTRIBUTE_CODE = 'weight_configurable';
    const POWERBODY_MIXED_WEIGHT_ATTRIBUTE_CODE = 'other';

    private $client;

    private $eavConfig;

    private $eavSetupFactory;

    public function __construct(
        ClientInterface $client,
        EavConfig $eavConfig,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->client = $client;
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function getAttributes() : array
    {
        return $this->client->call('bridge.getAttributes',
            ['attribute_code' => array_keys($this->getMappedAttributesArray())]
        );
    }

    public function getMappedAttributesArray() : array
    {
        return [
            self::POWERBODY_COLOR_ATTRIBUTE_CODE   => self::DROPCLIENT_COLOR_ATTRIBUTE_CODE,
            self::POWERBODY_FLAVOUR_ATTRIBUTE_CODE => self::DROPCLIENT_FLAVOUR_ATTRIBUTE_CODE,
            self::POWERBODY_SIZE_ATTRIBUTE_CODE    => self::DROPCLIENT_SIZE_ATTRIBUTE_CODE,
            self::POWERBODY_WEIGHT_ATTRIBUTE_CODE  => self::DROPCLIENT_WEIGHT_ATTRIBUTE_CODE,
            self::POWERBODY_MIXED_WEIGHT_ATTRIBUTE_CODE => self::DROPCLIENT_MIXED_WEIGHT_ATTRIBUTE_CODE,
        ];
    }

    public function getAttributesWithOptionsArray() : array
    {
        $attributesArray = [];

        foreach ($this->getMappedAttributesArray() as $baseAttributeCode => $clientAttributeCode) {
            /* @var $attributeModel \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            $attributeModel = $this->eavConfig->getAttribute('catalog_product', $clientAttributeCode);
            $attributesArray[$baseAttributeCode] = $attributeModel->getSource()->getAllOptions();
        }

        return $attributesArray;
    }

    public function getAttributeOptionIdByAttributeCodeAndValue(
        string $attributeCode, string $attributeOptionValue) : string
    {
        $attributeModel = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

        return (string)$attributeModel->getSource()->getOptionId($attributeOptionValue);
    }

    public static function getImportedAttributes() : array
    {
        return [
            self::DROPCLIENT_COLOR_ATTRIBUTE_CODE,
            self::DROPCLIENT_FLAVOUR_ATTRIBUTE_CODE,
            self::DROPCLIENT_SIZE_ATTRIBUTE_CODE,
            self::DROPCLIENT_WEIGHT_ATTRIBUTE_CODE,
            self::DROPCLIENT_MIXED_WEIGHT_ATTRIBUTE_CODE,
        ];
    }

}
