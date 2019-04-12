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
class Order extends \Magento\Framework\App\Helper\AbstractHelper
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
     * Order constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\objectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Json\Helper\Data $jdecode
     * @param \Ced\Etsy\Model\ResourceModel\Orders\CollectionFactory $etsyOrder
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Framework\App\Cache\TypeListInterface $cache
     * @param Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\objectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Json\Helper\Data $jdecode,
        \Ced\Etsy\Model\ResourceModel\Orders\CollectionFactory $etsyOrder,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
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
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->product = $product;
        $this->jdecode = $jdecode;
        $this->etsyOrder = $etsyOrder;
        $this->orderService = $orderService;
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->cache = $cache;
        $this->datahelper = $dataHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ManagerInterface
     */
    public function hasNewOrders()
    {
        try {
            /*$cacheType = ['translate', 'config', 'block_html', 'config_integration',
                'reflection', 'db_ddl', 'layout', 'eav', 'config_integration_api',
                'full_page', 'collections', 'config_webservice'];
            foreach ($cacheType as $cache) {
                $this->cache->cleanType($cache);
            }*/

            $storeId = $this->scopeConfig->getValue('etsy_config/etsy_setting/storeid');
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $store = $this->storeManager->getStore($storeId);
            $shopId = $this->scopeConfig->getValue('etsy_config/etsy_setting/shop_name');
            if (!$shopId) {
                return $this->messageManager->addErrorMessage("please enter the shop name");
            }
            $result = $this->datahelper->ApiObject()->findAllShopReceiptsByStatus(
                [
                    'params' => ['shop_id' => $shopId, 'status' => 'open']
                ]
            );
            
            if (isset($result['results']) && !empty($result['results'])) {
                $count = 0;
                foreach ($result['results'] as $result) {
                    $email = $result['buyer_email'];
                    $etsyOrderId = $result['order_id'];
                    $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
                    $resultdata = $this->etsyOrder->create()->addFieldToFilter('etsy_order_id', $etsyOrderId);
                    if (!$this->validateString($resultdata->getData())) {
                        $ncustomer = $this->assignCustomer($result, $customer, $websiteId);
                        if (!$ncustomer) {
                            return $this->messageManager->addErrorMessage("Error while create coustomer");
                        } else {
                            $count = $this->generateQuote($store, $ncustomer, $result, $count);
                        }
                    }
                }
                if ($count > 0) {
                    $this->messageManager->addSuccessMessage($count . " New Etsy Orders fetched successfully");
                    $this->notificationSuccess($count);
                } else {
                    return $this->messageManager->addSuccessMessage("No New Etsy Orders");
                }
            } else {
                return $this->messageManager->addSuccessMessage("No New Etsy Orders");
            }
        } catch (\Exception $e) {
            return $this->messageManager->addSuccessMessage($e->getMessage());
        }
    }

    /**
     * @param $string
     * @return bool
     */
    public function validateString($string)
    {
        $stringValidation = (isset($string) && !empty($string)) ? true : false;
        return $stringValidation;
    }

    /**
     * @param $result
     * @param $customer
     * @param $websiteId
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function assignCustomer($result, $customer, $websiteId)
    {
        if (!$this->validateString($customer->getId())) {
            try {
                $nameArray = explode(' ', $result['name']);
                $firstname = $lastname = '';
                $lastArray = [];
                foreach ($nameArray as $value) {
                    if ($value != '') {
                        if ($firstname == '') {
                            $firstname = $value;
                        } else {
                            $lastArray[] = $value;
                        }       
                    }
                }
                $lastname = implode(' ', $lastArray);
                $firstname = $firstname == '' ? "EtsyCustomer" : $firstname;
                $lastname = $lastname == '' ? "EtsyCustomer" : $lastname;
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($result['buyer_email']);
                $customer->setFirstname($firstname);
                $customer->setLastname($lastname);
                $customer->setPassword("etsypassword");
                $customer->save();
                return $customer;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return false;
            }
        } else {
            $nCustomer = $this->customerRepository->getById($customer->getId());
            return $nCustomer;
        }
    }

    /**
     * @param $store
     * @param $ncustomer
     * @param $result
     * @param $count
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateQuote($store, $ncustomer, $result, $count)
    {
        $cart_id = $this->cartManagementInterface->createEmptyCart();
        $quote = $this->cartRepositoryInterface->get($cart_id);
        $quote->setStore($store);
        $quote->setCurrency();
        $customer = $this->customerRepository->getById($ncustomer->getId());
        $quote->assignCustomer($customer);
        $receiptId = $result['receipt_id'];
        $shippingcost = $result['total_shipping_cost'];
        $itemsArray = $this->datahelper->ApiObject()->findAllShop_Receipt2Transactions(['params' => ['receipt_id' =>
            (int)$receiptId]]);
       /* $this->etsyOrderModel->setPurchaseOrderId($result ['purchaseOrderId'])
            ->setDeliverBy($deliver_by)
            ->setOrderPlaceDate($order_place)
            ->setStatus($order['orderLines']['orderLine'][0]['orderLineStatuses']['orderLineStatus'][0]['status'])
            ->setOrderData(json_encode($order))
            ->setMerchantOrderId($order['customerOrderId']);
        */
        foreach ($itemsArray['results'] as $item) {
            if (isset($item['product_data']['sku'])) {
                $qty = $item ['quantity'];
                $productObj = $this->objectManager->get('Magento\Catalog\Model\Product');
                $product = $productObj->loadByAttribute('sku', $item['product_data']['sku']);
                if ($product) {
                    $product = $this->product->create()->load($product->getEntityId());
                    if ($product->getStatus() == '1') {
                        $stockRegistry = $this->objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
                        /* Get stock item */
                        $stock = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                        $stockstatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ?
                            ($stock->getQty() >= $item ['quantity'] ? true : false)
                            : false) : false;
                        if ($stockstatus) {
                            $productArray [] = $item;
                            $price = $item['price'];
                            $baseprice = $qty * $price;
                            $shippingcost += $item ['shipping_cost'];
                            $rowTotal = $price * $qty;
                            $product->setPrice($price)
                                ->setBasePrice($baseprice)
                                ->setOriginalCustomPrice($price)
                                ->setRowTotal($rowTotal)
                                ->setBaseRowTotal($rowTotal)
                                ->setTierPrice([]);
                            $quote->addProduct($product, (int)$qty);
                        }/*else{
                            $message='Out of stock';
                            $this->rejectOrder($item,$result,$message);
                        }*/
                    }
                }
            }
        }

        if (isset($productArray)) {
            $result['items'] = $productArray;
            $nameArray = explode(' ', $result['name']);
            $firstname = $lastname = '';
            $lastArray = [];
            foreach ($nameArray as $value) {
                if ($value != '') {
                    if ($firstname == '') {
                        $firstname = $value;
                    } else {
                        $lastArray[] = $value;
                    }       
                }
            }
            $lastname = implode(' ', $lastArray);
            $firstname = $firstname == '' ? "EtsyCustomer" : $firstname;
            $lastname = $lastname == '' ? "EtsyCustomer" : $lastname;
            $phone = 0;
            $countryCodeResponse = $this->objectManager->get('Ced\Etsy\Model\Config\Country')->getContryLabel
            ($result['country_id']);
            $countryCode = !empty($countryCodeResponse) ? $countryCodeResponse : "UK";
            $address = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'street' => $result['first_line'] . " " . $result['second_line'],
                'city' => $result['city'],
                'country_id' => $countryCode,
                'region' => $result['state'],
                'postcode' => $result ['zip'],
                'telephone' => $phone,
                'fax' => '',
                'save_in_address_book' => 1
            ];

            $quote->getBillingAddress()->addData($address);
            $shippingAddress = $quote->getShippingAddress()->addData($address);

            $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                ->setShippingMethod('shipbyetsy_shipbyetsy');
            $quote->setPaymentMethod('paybyetsy');
            $quote->setInventoryProcessed(false);
            $quote->save();
            $quote->getPayment()->importData([
                'method' => 'paybyetsy'
            ]);
            $quote->collectTotals()->save();
            foreach ($quote->getAllItems() as $item) {
                $item->setDiscountAmount(0);
                $item->setBaseDiscountAmount(0);
                $item->setOriginalCustomPrice($item->getPrice())
                    ->setOriginalPrice($item->getPrice())
                    ->save();
            }
            $order = $this->cartManagementInterface->submit($quote);
            $preFix = $this->scopeConfig->getValue('etsy_config/etsy_order/order_id_prefix');
            $orderId = $preFix.$order->getIncrementId();
            $order->setShippingAmount($shippingcost)->setBaseShippingAmount($shippingcost)->save();
            $count = isset($order) ? $count + 1 : $count;
            foreach ($order->getAllItems() as $item) {
                $item->setOriginalPrice($item->getPrice())
                    ->setBaseOriginalPrice($item->getPrice())
                    ->save();
            }

            // after save order
            $order_place = date('Y-m-d', $result['creation_tsz']);
            $orderData = [
                'etsy_order_id' => $result['order_id'],
                'order_place_date' => $order_place,
                'magento_order_id' => $order->getIncrementId(),
                'status' => 'acknowledge',
                'order_data' => serialize($result)
            ];
            $model = $this->objectManager->create('Ced\Etsy\Model\Orders')->addData($orderData);
            $model->save();
            $this->sendMail($result['order_id'], $order->getIncrementId(), $order_place);
            $this->generateInvoice($order);
        }
        return $count;
    }

    /**
     * @param $etsyOrderId
     * @param $mageOrderId
     * @param $placeDate
     * @return bool
     */
    public function sendMail($etsyOrderId, $mageOrderId, $placeDate)
    {
        $body = '<table cellpadding="0" cellspacing="0" border="0">
            <tr> <td> <table cellpadding="0" cellspacing="0" border="0">
                <tr> <td class="email-heading">
                    <h1>You have a new order from Etsy.</h1>
                    <p> Please review your admin panel."</p>
                </td> </tr>
            </table> </td> </tr>
            <tr> 
                <td>
                    <h4>Merchant Order Id' . $etsyOrderId . '</h4>
                </td>
                <td>
                    <h4>Magneto Order Id' . $mageOrderId . '</h4>
                </td>
                <td>
                    <h4>Order Place Date' . $placeDate . '</h4>
                </td>
            </tr>  
        </table>';
        $to_email = $this->scopeConfig->getValue('etsy_config/etsy_order/order_notify_email');
        $subject = 'Imp: New Etsy Order Imported';
        $senderEmail = 'ebayadmin@cedcommerce.com';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $senderEmail . '' . "\r\n";
        mail($to_email, $subject, $body, $headers);
        return true;
    }

    /**
     * @param $order
     */
    public function generateInvoice($order)
    {
        $invoice = $this->objectManager->create(
            'Magento\Sales\Model\Service\InvoiceService')->prepareInvoice(
            $order);
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->objectManager->create(
            'Magento\Framework\DB\Transaction')->addObject(
            $invoice)->addObject($invoice->getOrder());
        $transactionSave->save();
        $order->addStatusHistoryComment(__(
            'Notified customer about invoice #%1.'
            , $invoice->getId()))->setIsCustomerNotified(true)->save();
        $order->setStatus('processing')->save();
    }

    /**
     * @param $order
     * @param $cancelleditems
     */
    public function generateShipment($order, $cancelleditems)
    {
        $shipment = $this->_prepareShipment($order, $cancelleditems);
        if ($shipment) {
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            try {
                $transactionSave = $this->objectManager->create('Magento\Framework\DB\Transaction')
                    ->addObject($shipment)->addObject($shipment->getOrder());
                $transactionSave->save();
                $order->setStatus('complete')->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage('Error in saving shipping:' . $e->getMessage());
            }
        }
    }

    /**
     * @param $order
     * @param $cancelleditems
     * @return bool
     */
    public function _prepareShipment($order, $cancelleditems)
    {
        $shipment = $this->objectManager->get('Magento\Sales\Model\Order\ShipmentFactory')
            ->create($order, isset($cancelleditems) ? $cancelleditems : [], []);
        if (!$shipment->getTotalQty()) {
            return false;
        }
        return $shipment;
    }

    /**
     * @param $order
     * @param $cancelleditems
     */

    public function generateCreditMemo($order, $cancelleditems)
    {
        foreach ($order->getAllItems() as $orderItems) {
            $items_id = $orderItems->getId();
            $order_id = $orderItems->getOrderId();
        }
        $creditmemoLoader = $this->creditmemoLoaderFactory->create();
        $creditmemoLoader->setOrderId($order_id);
        foreach ($cancelleditems as $item_id => $cancelQty) {
            $creditmemo[$item_id] = ['qty' => $cancelQty];
        }
        $items = ['items' => $creditmemo,
            'do_offline' => '1',
            'comment_text' => 'Etsy Cancelled Orders',
            'adjustment_positive' => '0',
            'adjustment_negative' => '0'];
        $creditmemoLoader->setCreditmemo($items);
        $creditmemo = $creditmemoLoader->load();
        $creditmemoManagement = $this->objectManager->create('Magento\Sales\Api\CreditmemoManagementInterface');
        if ($creditmemo) {
            $creditmemo->setOfflineRequested(true);
            $creditmemoManagement->refund($creditmemo, true);
        }
    }

    /**
     * @param $count
     * @return void
     */
    public function notificationSuccess($count)
    {
        $model = $this->objectManager->create('\Magento\AdminNotification\Model\Inbox');
        $date = date("Y-m-d H:i:s");
        $model->setData('severity', 4);
        $model->setData('date_added', $date);
        $model->setData('title', "New Etsy Orders");
        $model->setData('description', "Congratulation !! You have received " . $count . " new orders form Etsy");
        $model->setData('url', "#");
        $model->setData('is_read', 0);
        $model->setData('is_remove', 0);
        $model->save();
        return true;
    }

    public function rejectOrder($item,$result,$message){

    }
}
