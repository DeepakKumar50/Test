<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Etsy
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class InstallSchema
 *
 * @package Ced\Etsy\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('etsy_profile');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
        /**
         * Create table 'etsy_profile'
         */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'profile_code',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Profile Code'
                )
                ->addColumn(
                    'profile_status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => true, 'default' => 1],
                    'Profile Status'
                )
                ->addColumn(
                    'profile_name',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Profile Name'
                )
                ->addColumn(
                    'profile_category',
                    Table::TYPE_TEXT,
                    500,
                    ['nullable' => true, 'default' => ''],
                    'Profile Category'
                )
                ->addColumn(
                    'recipient',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Recipient'
                )
                ->addColumn(
                    'occasion',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Cccasion'
                )
                ->addColumn(
                    'tags',
                    Table::TYPE_TEXT,
                    500,
                    ['nullable' => true, 'default' => ''],
                    'Tags'
                )
                ->addColumn(
                    'config_attributes',
                    Table::TYPE_TEXT,
                    1000,
                    ['nullable' => true, 'default' => ''],
                    'Config Attributes'
                )
                ->addColumn(
                    'profile_req_opt_attribute',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Profile Required And Optional Attribute'
                )->addIndex(
                    $setup->getIdxName(
                        'etsy_profile',
                        ['profile_code'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['profile_code'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment('Profile Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('etsy_profile_products');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'etsy_profile_products'
             */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'profile_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Profile Id'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )
                ->addForeignKey(
                    $setup->getFkName('etsy_profile_products', 'profile_id', 'etsy_profile', 'id'),
                    'profile_id',
                    $setup->getTable('etsy_profile'),
                    'id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName(
                        'etsy_profile_products',
                        ['profile_id', 'product_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['profile_id', 'product_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addIndex(
                    $setup->getIdxName(
                        'etsy_profile_products',
                        ['product_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['product_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment('Profile Products Table')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('etsy_cron_scheduler');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'etsy_cron_scheduler'
             */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                    ],
                    'ID'
                )->addColumn(
                    'product_ids', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [ 
                                'nullable' => false,
                                ],
                    'Product Ids' 
                )
                ->addColumn(
                    'start_time', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [
                                'nullable' => false
                                ],
                    'Start Time'
                )
                ->addColumn(
                    'finish_time', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [
                                'nullable' => false
                                ],
                    'Finish Time'
                )
                ->addColumn(
                    'cron_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [ 
                                'nullable' => false,
                                ],
                    'Cron Status' 
                )
                ->addColumn(
                    'msg', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                                'nullable' => false,
                                ],
                    'Message'
                );
            
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'etsy_orders'
         */
        $tableName = $installer->getTable('etsy_orders');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'etsy_orders'
             */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'etsy_order_id',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Etsy Order Id'
                )
                ->addColumn(
                    'magento_order_id',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Magento Order Id'
                )
                ->addColumn(
                    'order_place_date',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'Order Place Date'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    100,
                    ['nullable' => true, 'default' => ''],
                    'Etsy Order Status'
                )
                ->addColumn(
                    'order_data',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Order Data'
                )
                ->addColumn(
                    'shipment_data',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Order Shipment Data'
                )->setComment('Etsy Orders')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('etsy_product_change');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'etsy_product_change'
             */
            $table = $installer->getConnection()->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Profile Status'
                )
                ->addColumn(
                    'old_value',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Old Value'
                )
                ->addColumn(
                    'new_value',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'New Value'
                )
                ->addColumn(
                    'action',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Action'
                )
                ->addColumn(
                    'cron_type',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => true, 'default' => ''],
                    'Cron type'
                )
                ->setComment('Etsy Product Change')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }
    }
}
