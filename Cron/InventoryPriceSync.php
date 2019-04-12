<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Etsy
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Cron;

use Etsy\EtsyRequestException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class InventoryPriceSync
{
    public $logger;

    public $autoSync;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_objectManager = $objectManager;
        $this->logger = $logger;
        $this->stockRegistry = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
        $this->helper = $this->_objectManager->get('Ced\Etsy\Helper\Data');
        $this->etsyhelper = $this->_objectManager->get('Ced\Etsy\Helper\Etsy');
        $this->scopeConfig = $scopeConfig;
        $this->autoSync = $this->scopeConfig->getValue('etsy_config/product_sync/inv_sync');
    }

    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->autoSync){
            $profileProductIds = [];
            $ids = [];
            $error = '';
            try {
                $cronModel = $this->_objectManager->get('Ced\Etsy\Model\CronScheduler')->getCollection()
                    ->addFieldToFilter('cron_status', 'scheduled')->getFirstItem();
                if (!empty($cronModel->getData())) {
                    $startTime = date("Y-m-d H:i:s");
                    $idString = $cronModel->getProductIds();
                    $prodIds = explode(',', $idString);
                    foreach ($prodIds as $id) {
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        $listingID = $product->getEtsyListingId();
                        try {
                            if (!empty($product)) {
                                $qty = $this->_objectManager->get('Magento\CatalogInventory\Api\StockStateInterface')
                                    ->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                                if ((int)$qty > 0) {
                                    $data = [
                                        'quantity' => (int)$qty,
                                        'state' => 'active'
                                    ];
                                } else {
                                    $data = ['state' => 'inactive'];
                                }
                                $this->_objectManager->create('Ced\Etsy\Helper\Data')->ApiObject()->updateListing(
                                    [
                                        'params' => ['listing_id' => (int)$listingID],
                                        'data' => $data
                                    ]
                                );
                            }
                            $ids[] = $id;
                        } catch (EtsyRequestException $e) {
                            $error[] = $id . " has exception " . $e->getLastResponse();
                        } catch (\Exception $e) {
                            $error[] = $id . " has exception " . $e->getMessage();
                        }
                    }
                    $finishTime = date("Y-m-d H:i:s");
                    if (!empty($ids)) {
                        $msg = 'Successfully sync Inventory for Ids: '.implode(', ', $ids);
                        $etsyCronModel = $this->_objectManager->create('Ced\Etsy\Model\CronScheduler')->load($cronModel->getId());
                        $etsyCronModel->setStartTime($startTime);
                        $etsyCronModel->setFinishTime($finishTime);
                        $etsyCronModel->setCronStatus('success');
                        $etsyCronModel->setError($msg);
                        $etsyCronModel->getResource()->save($etsyCronModel);
                        $this->logger->info('Etsy_InventorySync: Success -'.$msg);
                    }
                    if (!empty($error)) {
                        $msg = implode(', ', $error);
                        $etsyCronModel = $this->_objectManager->create('Ced\Etsy\Model\CronScheduler')->load($cronModel->getId());
                        $etsyCronModel->setStartTime($startTime);
                        $etsyCronModel->setFinishTime($finishTime);
                        $etsyCronModel->setCronStatus('fail');
                        $etsyCronModel->setError($msg);
                        $etsyCronModel->getResource()->save($etsyCronModel);
                        $this->logger->info('Etsy_InventorySync: Error -' . $msg);
                    }
                } else {
                    $cronData = $this->_objectManager->get('Ced\Etsy\Model\CronScheduler')->getCollection();
                    if ($cronData->getSize() > 0) {
                        $time = $cronData->getFirstItem()->getStartTime();
                        $day = date("d", strtotime($time));
                        $month = date("m", strtotime($time));
                        $year = date("Y", strtotime($time));
                        $today = date("d");
                        $currentMonth = date("m");
                        $currentYear = date("Y");
                        if ($today > $day) {
                            $check = true;
                        } elseif ($currentMonth > $month || $currentYear > $year) {
                            $check = true;
                        } else {
                            $check = false;
                        }
                        if ($check) {
                            $model = $this->_objectManager->create('Ced\Etsy\Model\CronScheduler');
                            $connection = $model->getResource()->getConnection()->truncateTable('etsy_cron_scheduler');
                            $result = $this->_objectManager->create('Ced\Etsy\Model\Profileproducts')->getCollection()
                                ->getData();
                            foreach ($result as $val) {
                                $profileProductIds[] = $val['product_id'];
                            }
                            $pids = $this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection()
                                ->addAttributeToFilter('etsy_product_status', ['in' => ['uploaded']])->getAllIds();
                            $productIds = array_intersect($profileProductIds, $pids);
                            $productids = array_chunk($productIds, 20);
                            foreach ($productids as $ids) {
                                $idstr = implode(', ', $ids);
                                $cronModel = $this->_objectManager->create('Ced\Etsy\Model\CronScheduler');
                                $cronModel->setProductIds($idstr);
                                $cronModel->setCronStatus('scheduled');
                                $cronModel->getResource()->save($cronModel);
                            }
                            $msg = 'Table truncate and batch scheduled successfully.';
                            $this->logger->info('Etsy_InventorySync: Error -' . $msg);
                        }
                    } else {
                        $result = $this->_objectManager->create('Ced\Etsy\Model\Profileproducts')->getCollection()
                            ->getData();
                        foreach ($result as $val) {
                            $profileProductIds[] = $val['product_id'];
                        }
                        $pids = $this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection()
                            ->addAttributeToFilter('etsy_product_status', ['in' => ['uploaded']])->getAllIds();
                        $productIds = array_intersect($profileProductIds, $pids);
                        $productids = array_chunk($productIds, 20);
                        foreach ($productids as $ids) {
                            $idstring = implode(', ', $ids);
                            $cronModel = $this->_objectManager->create('Ced\Etsy\Model\CronScheduler');
                            $cronModel->setCronStatus('scheduled');
                            $cronModel->setProductIds($idstring);
                            $cronModel->getResource()->save($cronModel);
                        }
                        $msg = "first time scheduler run";
                        $this->logger->info('Etsy_InventorySync: Error -' . $msg);
                    }
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $this->logger->info('Etsy_InventorySync: exception -' . $msg);
            }
        } else {

        }
        return true;
    }
}