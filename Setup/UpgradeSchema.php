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
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Ced\Etsy\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.1', '<=')) {

            if ($setup->getConnection()->isTableExists($setup->getTable('etsy_profile')) == true) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('etsy_profile'),
                    'config_attributes',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 1000,
                        'nullable' => true,
                        'comment' => 'Config Attributes'
                    ]
                );
            }
            if ($setup->getConnection()->isTableExists($setup->getTable('etsy_orders')) == true) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('etsy_orders'),
                    'reason',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 1000,
                        'nullable' => true,
                        'comment' => 'Reason'
                    ]
                );
            }
            $tableName = $setup->getTable('etsy_shipping_templates');


            if ($setup->getConnection()->isTableExists($tableName) != true) {
                /**
                 * Create table 'etsy_orders'
                 */
                $table = $setup->getConnection()->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        100,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'ID'
                    )
                    ->addColumn(
                        'title',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Title'
                    )
                    ->addColumn(
                        'origin_country_id',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Origin Country Id'
                    )
                    ->addColumn(
                        'destination_country_id',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false],
                        'Destination Country Id'
                    )
                    ->addColumn(
                        'primary_cost',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Primary Cost'
                    )
                    ->addColumn(
                        'secondary_cost',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Secondary Cost'
                    )
                    ->addColumn(
                        'destination_region_id',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Destination Region id'
                    ) ->addColumn(
                        'min_processing_days',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Min Processing Days'
                    )
                    ->addColumn(
                        'max_processing_days',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Max Processing Days'
                    ) ->addColumn(
                        'processing_days_display_label',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Processing Days Display Label'
                    )
                    ->addColumn(
                        'user_id',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'User Id'
                    )
                    ->addColumn(
                        'shipping_template_id',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'shipping Template Id'
                    )->setComment('Etsy Shipping Templates')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

                $setup->getConnection()->createTable($table);
            }
            $tableName2 = $setup->getTable('etsy_etsylogs');
            if ($setup->getConnection()->isTableExists($tableName2) != true) {
                /**
                 * Create table 'etsy_orders'
                 */
                $table2 = $setup->getConnection()->newTable($tableName2)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        100,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'ID'
                    )
                    ->addColumn(
                        'log_type',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Log Type'
                    )
                    ->addColumn(
                        'log_sub_type',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Log Sub Type'
                    )
                    ->addColumn(
                        'log_date',
                        Table::TYPE_DATE,
                        100,
                        ['nullable' => false],
                        'Log Date'
                    )
                    ->addColumn(
                        'log_value',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Log Value'
                    )
                    ->addColumn(
                        'log_comment',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Log Comment'
                    )
                    ->setComment('Etsy Etsylogs')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

                $setup->getConnection()->createTable($table2);
            }
            $tableName3 = $setup->getTable('etsy_third_party_products');
            if ($setup->getConnection()->isTableExists($tableName3) != true) {
                /**
                 * Create table 'etsy_orders'
                 */
                $table3 = $setup->getConnection()->newTable($tableName3)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        100,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'ID'
                    )
                    ->addColumn(
                        'etsy_listing_id',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => false, 'default' => ''],
                        'Etsy Listing Id'
                    )
                    ->addColumn(
                        'etsy_sku',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Etsy Sku'
                    )
                    ->addColumn(
                        'etsy_data',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Etsy Data'
                    )
                    ->setComment('Etsy Third Party Products')->setOption('type', 'InnoDB')->setOption('charset', 'utf8');

                $setup->getConnection()->createTable($table3);
            }
        }
        $setup->endSetup();
    }
}
