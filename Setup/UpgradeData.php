<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use \Magento\Catalog\Model\Product;
use \Magento\Catalog\Model\Category;
use \Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use \Magento\Catalog\Model\Product\Type as ProductType;
use \Powerbody\Bridge\Service\Fixer\ProductTaxClass as TaxFixer;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    private $taxFixer;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        TaxFixer $taxFixer
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->taxFixer = $taxFixer;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            /* @var $eavSetup EavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $attributesToAddArray = [
                'powerbody_color'   => 'Color',
                'powerbody_size'    => 'Size',
                'powerbody_weight'  => 'Weight',
                'powerbody_flavour' => 'Flavour',
            ];

            $i = 0;
            /**
             * Add attributes to the eav/attribute
             */
            foreach ($attributesToAddArray as $attributeCode => $attributeLabel) {
                $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);
                if (false === $attributeId) {
                    $eavSetup->addAttribute(
                        Product::ENTITY,
                        $attributeCode,
                        [
                            'group'                     => 'General',
                            'type'                      => 'int',
                            'backend'                   => '',
                            'frontend'                  => '',
                            'label'                     => $attributeLabel,
                            'input'                     => 'select',
                            'class'                     => '',
                            'source'                    => '',
                            'global'                    => Attribute::SCOPE_GLOBAL,
                            'visible'                   => true,
                            'required'                  => false,
                            'user_defined'              => true,
                            'default'                   => '',
                            'searchable'                => false,
                            'filterable'                => false,
                            'comparable'                => false,
                            'visible_on_front'          => false,
                            'used_in_product_listing'   => true,
                            'unique'                    => false,
                            'apply_to'                  => ''
                        ]
                    );
                } else {
                    $eavSetup->updateAttribute(Product::ENTITY, $attributeId, 'backend_type', 'int');
                    $eavSetup->updateAttribute(Product::ENTITY, $attributeId, 'frontend_input', 'select');
                }
                $eavSetup->addAttributeToSet(Product::ENTITY, 'Default', 'General', $attributeCode, ++$i);
            }
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {

            /* @var $eavSetup EavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $attributeCode = 'is_updated_while_import';
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);
            if (false === $attributeId) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    [
                        'group'                      => 'General',
                        'type'                       => 'int',
                        'backend'                    => '',
                        'frontend'                   => '',
                        'label'                      => 'Is updated while import',
                        'input'                      => 'boolean',
                        'class'                      => '',
                        'source'                     => '',
                        'global'                     => Attribute::SCOPE_GLOBAL,
                        'visible'                    => true,
                        'required'                   => false,
                        'user_defined'               => true,
                        'default'                    => '1',
                        'searchable'                 => false,
                        'filterable'                 => false,
                        'comparable'                 => false,
                        'visible_on_front'           => false,
                        'visible_in_advanced_search' => false,
                        'used_in_product_listing'    => false,
                        'unique'                     => false,
                        'apply_to'                   => ProductType::TYPE_SIMPLE
                    ]
                );
            }
            $eavSetup->addAttributeToSet(Product::ENTITY, 'Default', 'General', $attributeCode);

            $attributeCode = 'ean';
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);
            if (false === $attributeId) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    [
                        'group'                      => 'General',
                        'type'                       => 'varchar',
                        'label'                      => 'Ean',
                        'input'                      => 'text',
                        'global'                     => Attribute::SCOPE_GLOBAL,
                        'visible'                    => true,
                        'required'                   => false,
                        'user_defined'               => true,
                        'default'                    => '',
                        'searchable'                 => false,
                        'filterable'                 => false,
                        'comparable'                 => false,
                        'visible_on_front'           => false,
                        'visible_in_advanced_search' => false,
                        'used_in_product_listing'    => false,
                        'unique'                     => false,
                        'is_html_allowed_on_front'   => false,
                        'is_visible_on_front'        => false,
                        'used_for_sort_by'           => false,
                        'apply_to'                   => ProductType::TYPE_SIMPLE
                    ]
                );
            }
            $eavSetup->addAttributeToSet(Product::ENTITY, 'Default', 'General', $attributeCode);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {

            /* @var $eavSetup EavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $attributeCode = 'import_updated_at';
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);

            $attributeDataArray = [
                'group'                      => 'General',
                'type'                       => 'datetime',
                'backend'                    => '',
                'frontend'                   => '',
                'label'                      => 'Import updated at',
                'input'                      => 'date',
                'class'                      => '',
                'source'                     => '',
                'global'                     => Attribute::SCOPE_GLOBAL,
                'visible'                    => false,
                'required'                   => true,
                'user_defined'               => true,
                'default'                    => '',
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing'    => false,
                'unique'                     => false,
                'apply_to'                   => 'simple,configurable'
            ];
            if (false === $attributeId) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    $attributeDataArray
                );
            } else {
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $attributeId,
                    $attributeDataArray
                );
            }
            $eavSetup->addAttributeToSet(Product::ENTITY, 'Default', 'General', $attributeCode);
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {

            /* @var $eavSetup EavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $attributeCode = 'is_imported';
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);

            $attributeDataArray =  [
                'group'                      => 'General',
                'type'                       => 'int',
                'backend'                    => '',
                'frontend'                   => '',
                'label'                      => 'Is imported',
                'input'                      => 'boolean',
                'class'                      => '',
                'source'                     => '',
                'global'                     => Attribute::SCOPE_GLOBAL,
                'visible'                    => false,
                'required'                   => false,
                'user_defined'               => false,
                'default'                    => '0',
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => false,
                'visible_on_front'           => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing'    => false,
                'unique'                     => false,
                'apply_to'                   => 'simple,configurable'
            ];

            if (false !== $attributeId) {
                $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
            }

            $eavSetup->addAttribute(
                Product::ENTITY,
                $attributeCode,
                $attributeDataArray
            );

            $eavSetup->addAttributeToSet(Product::ENTITY, 'Default', 'General', $attributeCode);
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {

            /* @var $eavSetup EavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $attributeCode = 'portion_count';
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);

            $attributeDataArray =  [
                'group'                      => 'General',
                'type'                       => 'int',
                'label'                      => 'Servings per container',
                'input'                      => 'text',
                'global'                     => Attribute::SCOPE_GLOBAL,
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => true,
                'default'                    => '',
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => true,
                'visible_in_advanced_search' => false,
                'used_in_product_listing'    => true,
                'unique'                     => false,
                'is_html_allowed_on_front'   => true,
                'is_visible_on_front'        => true,
                'used_for_sort_by'           => false,
                'apply_to'                   => ''
            ];

            if (false !== $attributeId) {
                $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
            }

            $eavSetup->addAttribute(
                Product::ENTITY,
                $attributeCode,
                $attributeDataArray
            );

            $eavSetup->addAttributeToSet(Product::ENTITY, 'Default', 'General', $attributeCode);
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) {

            /* @var $eavSetup EavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $attributeCode = 'is_imported';
            $attributeDataArray =  [
                'type' => 'int',
                'label' => 'Is category imported',
                'input' => 'boolean',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'visible' => false,
                'default' => '0',
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Display Settings',
            ];

            $eavSetup->addAttribute(
                Category::ENTITY,
                $attributeCode,
                $attributeDataArray
            );
        }

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            /* @var $eavSetup EavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $attributeCode = 'powerbody_mixed_weight';
            $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);

            if (false === $attributeId) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    [
                        'group'                     => 'General',
                        'type'                      => 'int',
                        'backend'                   => '',
                        'frontend'                  => '',
                        'label'                     => 'Mixed Weight',
                        'input'                     => 'select',
                        'class'                     => '',
                        'source'                    => '',
                        'global'                    => Attribute::SCOPE_GLOBAL,
                        'visible'                   => true,
                        'required'                  => false,
                        'user_defined'              => true,
                        'default'                   => '',
                        'searchable'                => false,
                        'filterable'                => false,
                        'comparable'                => false,
                        'visible_on_front'          => false,
                        'used_in_product_listing'   => true,
                        'unique'                    => false,
                        'apply_to'                  => ''
                    ]
                );
            }

            $eavSetup->addAttributeToSet(Product::ENTITY, 'Default', 'General', $attributeCode, 100);
        }

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->taxFixer->fixConfigurableProductsTaxClasses();
        }
    }
}
