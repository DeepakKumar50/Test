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
 * @category    Ced
 * @package     Ced_Etsy
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Cron;

class UpdateInventory
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * OM
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Config Manager
     * @var \Ced\Etsy\Helper\Data
     */
    public $helper;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var
     */
    public $helperData;

    public $productchange;

    /**
     * UploadProducts constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Ced\Etsy\Helper\Logger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Ced\Etsy\Helper\Data $helperData,
        \Ced\Etsy\Model\Productchange $productchange
    ) {
        $this->scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->objectManager = $objectManager;
        $this->helper = $this->objectManager->get('Ced\Etsy\Helper\Data');
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->helperData = $helperData;
        $this->productchange = $productchange;
    }


    /**
     * Execute
     * @return bool
     */
    public function execute()
    {
        $scopeConfigManager = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $autoSync = $scopeConfigManager->getValue('etsy_config/product_sync/auto_sync');
        if ($autoSync) {
            $collection = $this->productchange->getCollection();
            $type = \Ced\Etsy\Model\Productchange::CRON_TYPE_INVENTORY;
            $collection->addFieldToFilter('cron_type', $type);
            $ids = [];
            foreach ($collection as $pchange){
                $ids[]['id']= $pchange->getProductId();
            }
            $inventory = $this->objectManager->get('Ced\Etsy\Helper\Data')->updateInventoryOnEtsy($ids);

            if($inventory){
                $this->productchange->deleteFromProductChange($ids, $type);
                $this->logger->logger("Etsy Cron" , "Etsy Inventory Cron" , 'Success - '.var_export($inventory),' Inventory Cron Success');
                return true;
            }
            $this->logger->logger("Etsy Cron" , "Etsy Inventory Cron" , 'Failure - '.var_export($inventory,true),' Inventory Cron Failure');
            return false;

        } else {
            $this->logger->logger("Etsy Cron" , "Etsy Inventory Cron" , 'Disabled',' Inventory Cron Failure');
            return false;
        }
    }
}
