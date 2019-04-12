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

namespace Ced\Etsy\Helper;

use Magento\Framework\Message\ManagerInterface;

/**
 * Class Order
 * @package Ced\Etsy\Helper
 */
class ShippingTemplate extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\objectManagerInterface
     */
    public $objectManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jdecode;
    /**
     * @var \Ced\Etsy\Model\ResourceModel\Orders\CollectionFactory
     */
    public $etsyOrder;
    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    public $orderService;
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory
     */
    public $creditmemoLoaderFactory;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    public $cartManagementInterface;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepositoryInterface;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    public $cache;
    /**
     * @var Data
     */
    public $datahelper;
    /**
     * @var ManagerInterface
     */
    public $messageManager;

    /**
     * ShippingTemplate constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\objectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Json\Helper\Data $jdecode
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Ced\Etsy\Model\Shipping $shippingtemplate
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Framework\App\Cache\TypeListInterface $cache
     * @param Data $dataHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\objectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Json\Helper\Data $jdecode,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Ced\Etsy\Model\Shipping $shippingtemplate,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Framework\App\Cache\TypeListInterface $cache,
        Data $dataHelper,
        ManagerInterface $messageManager
    )
    {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->shippingTemplate = $shippingtemplate;
        $this->jdecode = $jdecode;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->cache = $cache;
        $this->datahelper = $dataHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ManagerInterface
     */
    public function fetchShippingTemplate()
    {
        try {
            $shopId = $this->scopeConfig->getValue('etsy_config/etsy_setting/shop_name');
            if (!$shopId) {
                return $this->messageManager->addErrorMessage("please enter the shop name");
            }
            $result =  $this->datahelper->ApiObject()->findAllUserShippingProfiles( [
                'params' => ['user_id' => '__SELF__']
            ]);
            if (isset($result['results']) && !empty($result['results']) ) {
                if (isset($result['count']) && $result['count']>0 ) {
                    foreach ($result['results'] as $key => $value){
                        $shipping= $this->shippingTemplate->load($value['shipping_template_id'],'shipping_template_id');
                        if($shipping->getId()){
                            $shipping->setTitle($value['title'])
                                ->setTitle($value['title'])
                                ->setShippingTemplateId($value['shipping_template_id'])
                                ->setOriginCountryId($value['origin_country_id'])
                                ->setMinProcessingDays($value['min_processing_days'])
                                ->setUserId($value['user_id'])
                                ->setProcessingDaysDisplayLabel($value['processing_days_display_label'])
                                ->setMaxProcessingDays($value['max_processing_days'])->save();
                        }else{
                            $shipping= $this->objectManager->create('\Ced\Etsy\Model\Shipping');
                            $shipping->setTitle($value['title'])
                                ->setTitle($value['title'])
                                ->setShippingTemplateId($value['shipping_template_id'])
                                ->setOriginCountryId($value['origin_country_id'])
                                ->setMinProcessingDays($value['min_processing_days'])
                                ->setUserId($value['user_id'])
                                ->setProcessingDaysDisplayLabel($value['processing_days_display_label'])
                                ->setMaxProcessingDays($value['max_processing_days'])->save();
                        }
                    }
                }else{
                    return $this->messageManager->addSuccessMessage("No New template");
                }
            } else {
                return $this->messageManager->addSuccessMessage("No New template");
            }
        } catch (\Exception $e) {
            return $this->messageManager->addSuccessMessage($e->getMessage());
        }
        return $this->messageManager->addSuccessMessage("Template successfully fetch");
    }

    /**
     * @param $param
     * @return ManagerInterface\
     * create the shipping template
     */
    public function createShippingTemplate($param){
        try {
            $shopId = $this->scopeConfig->getValue('etsy_config/etsy_setting/shop_name');
            if (!$shopId) {
                return $this->messageManager->addErrorMessage("please enter the shop name");
            }
            if(isset($param['id']) && !empty($param['id'])){
                $params=[
                    'title'=>$param['title'],
                    'origin_country_id'=>(int)$param['origin_country_id'],
                    'min_processing_days'=>(int)$param['min_processing_days'],
                    'max_processing_days'=>(int)$param['max_processing_days'],

                ];
                $result =  $this->datahelper->ApiObject()->updateShippingTemplate( [
                    'params' => ['shipping_template_id' =>(int)$param['shipping_template_id']],
                    'data' => $params
                ]);
            }else{
                $params=[

                    'title'=>$param['title'],
                    'origin_country_id'=>(int)$param['origin_country_id'],
                    'destination_country_id'=>(int)$param['destination_country_id'],
                    'primary_cost'=>(float)$param['primary_cost'],
                    'secondary_cost'=>(float)$param['secondary_cost'],
                    'min_processing_days'=>(int)$param['min_processing_days'],
                    'max_processing_days'=>(int)$param['max_processing_days']
                ];
                $result =  $this->datahelper->ApiObject()->createShippingTemplate( [
                    'params' => [],
                    'data' => $params
                ]);
            }
            if (isset($result['results']) && !empty($result['results']) ) {
                if (isset($result['count']) && $result['count']>0 ) {
                    foreach ($result['results'] as $key => $value){
                        foreach ($value['Entries'] as $key2 => $value2)
                        $shipping= $this->shippingTemplate->load($value['shipping_template_id'],'shipping_template_id');
                        if($shipping->getId()){
                            $shipping->setTitle($value['title'])
                                ->setTitle($value['title'])
                                ->setShippingTemplateId($value['shipping_template_id'])
                                ->setOriginCountryId($value['origin_country_id'])
                                ->setMinProcessingDays($value['min_processing_days'])
                                ->setUserId($value['user_id'])
                                ->setProcessingDaysDisplayLabel($value['processing_days_display_label'])
                                ->setMaxProcessingDays($value['max_processing_days'])->save();

                        }else{
                            $shipping= $this->objectManager->create('\Ced\Etsy\Model\Shipping');
                            $shipping->setTitle($value['title'])
                                ->setTitle($value['title'])
                                ->setShippingTemplateId($value['shipping_template_id'])
                                ->setOriginCountryId($value['origin_country_id'])
                                ->setMinProcessingDays($value['min_processing_days'])
                                ->setUserId($value['user_id'])
                                ->setPrimaryCost($value2['primary_cost'])
                                ->setSecondaryCost($value2['primary_cost'])
                                ->setDestinationCountryId($value2['destination_country_id'])
                                ->setUserId($value['user_id'])
                                ->setProcessingDaysDisplayLabel($value['processing_days_display_label'])
                                ->setMaxProcessingDays($value['max_processing_days'])->save();
                        }
                    }
                }

            }
        } catch (\Exception $e) {
            return $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->messageManager->addSuccessMessage("create or Update shipping template successfully");
    }

    /**
     * @param $param
     * @return ManagerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteShippingTemplate($param){
        $shippingresult= $this->shippingTemplate->load($param['id']);
        $result =$this->datahelper->ApiObject()->deleteShippingTemplate(
            [
                'params' => ['shipping_template_id' => $shippingresult->getshipping_template_id()],
                'data' => []
            ]
        );
        if (isset($result['results']) && empty($result['results']) ) {
            $this->shippingTemplate->load($shippingresult->getshipping_template_id(),'shipping_template_id')->delete();
            return $this->messageManager->addSuccessMessage("Deleted shipping template successfully");
        }
        return $this->messageManager->addErrorMessage("Some error occurred!");
    }
}
