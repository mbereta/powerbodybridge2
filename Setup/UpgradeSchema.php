<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Powerbody\Bridge\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the Cms module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->startSetup();
            $importedCategoryTableName = $setup->getTable('bridge_imported_category');
            if (false === $setup->getConnection()->isTableExists($importedCategoryTableName)) {
                $tableImportedCategory = $setup->getConnection()->newTable($importedCategoryTableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                        'Id'
                    )
                    ->addColumn(
                        'base_category_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false]
                    )
                    ->addColumn(
                        'client_category_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullbable' => true, 'default' => null],
                        'Client category Id'
                    )
                    ->addColumn(
                        'name',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true, 'default' => null],
                        'Name'
                    )
                    ->addColumn(
                        'is_selected',
                        Table::TYPE_SMALLINT,
                        null,
                        ['nullable' => false, 'default' => 0],
                        'Is selected'
                    )
                    ->addColumn(
                        'parent_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false, 'unsigned' => true],
                        'Parent Id'
                    )
                    ->addColumn(
                        'path',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true, 'default' => null],
                        'Path'
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
                    )->addColumn(
                        'updated_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE]
                    );
                $setup->getConnection()->createTable($tableImportedCategory);
            }

            $importedManufacturerTableName = $setup->getTable('bridge_imported_manufacturer');
            
            if (false === $setup->getConnection()->isTableExists($importedManufacturerTableName)) {
                $tableImportedManufacturer = $setup->getConnection()->newTable($importedManufacturerTableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                        'Id'
                    )
                    ->addColumn(
                        'base_manufacturer_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false],
                        'Base manufacturer Id'
                    )
                    ->addColumn(
                        'client_manufacturer_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullbable' => true, 'default' => null],
                        'Client category Id'
                    )
                    ->addColumn(
                        'name',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true, 'default' => null],
                        'Name'
                    )
                    ->addColumn(
                        'is_selected',
                        Table::TYPE_SMALLINT,
                        null,
                        ['nullable' => false, 'default' => 0],
                        'Is selected'
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
                    )->addColumn(
                        'updated_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE]
                    )
                    ->addColumn(
                        'dropshipping_status',
                        Table::TYPE_SMALLINT,
                        null,
                        ['nullable' => false, 'default' => 1],
                        'Dropshipping status'
                    );
                $setup->getConnection()->createTable($tableImportedManufacturer);
            }
            $setup->endSetup();
        }
        
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $setup->startSetup();
        
            $connection = $setup->getConnection();
            $tableName = $setup->getTable('bridge_export_orderentry');
        
            if (false === $connection->isTableExists($tableName)) {
                $table = $connection
                    ->newTable($tableName)
                    ->addColumn(
                        'entry_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary' => true,
                            'unsigned' => true,
                        ]
                    )
                    ->addColumn(
                        'order_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ]
                    )
                    ->addColumn(
                        'status',
                        Table::TYPE_SMALLINT,
                        null,
                        [
                            'default' => 0,
                            'nullable' => false,
                        ]
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'default' => Table::TIMESTAMP_INIT,
                            'nullable' => false,
                        ]
                    )
                    ->addColumn(
                        'updated_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'default' => Table::TIMESTAMP_UPDATE,
                        ]
                    )
                    ->addColumn(
                        'response_info',
                        Table::TYPE_TEXT,
                        null,
                        [
                            'nullable' => true,
                        ]
                    )
                    ->addForeignKey(
                        $setup->getIdxName($tableName, 'order_id'),
                        'order_id',
                        $setup->getTable('sales_order'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    );
            
                $connection->createTable($table);
            }
        
            $setup->endSetup();
        }
    }

}
