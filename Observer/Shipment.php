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
 * @category    Ced
 * @package     Ced_Etsy
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\Etsy\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class Shipment implements ObserverInterface
{
    /**
     * Request
     * @var  \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Shipment constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
        $this->registry = $registry;
        $this->objectManager = $objectManager;
    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $shippingMethod = $order->getShippingMethod();
        } catch (\Exception $e) {
            return $observer;
        }
        
        $trackArray = [];
        foreach ($shipment->getAllTracks() as $track) {
            $trackArray = $track->getData();
        }

        if (empty($trackArray)) {
            return $observer;
        }

        $datahelper = $this->objectManager->get('Ced\Etsy\Helper\Data');
        //$order = $this->objectManager->get('Magento\Sales\Model\Order')->load($trackArray['order_id']);
        $incrementId = $order->getIncrementId();

        $etsyOrder = $this->objectManager->get('Ced\Etsy\Model\Orders')->load($incrementId, 'magento_order_id');
        $etsyOrderId = $etsyOrder->getEtsyOrderId();
        if (empty($etsyOrderId)) {
            return $observer;
        }

        if ($etsyOrder->getEtsyOrderId()) {
            $orderData = $etsyOrder->getOrderData();
            $orderDataArray = unserialize($orderData);
            $receiptId = $orderDataArray['receipt_id'];
            $shipTodatetime = strtotime(date('Y-m-d H:i:s'));
            //after ack api end
            $trackNumber = (string)$trackArray['track_number'];
            $shippingCarrierUsed = $trackArray['carrier_code'];
            $itemsData = [];
            foreach ($order->getAllVisibleItems() as $item) {
                $merchantSku = $item->getSku();
                $quantityOrdered = $item->getQtyOrdered();
                $quantityToShip = $item->getQtyShipped();
                $itemsData [] = [
                    'sku' => $merchantSku,
                    'req_qty'=> $quantityOrdered,
                    'ship_qty' => $quantityToShip,
                    'cancel_quantity' => 0
                ];
            }
            $shipData = [
                'ship_todate' => $shipTodatetime,
                'carrier' => $shippingCarrierUsed,
                'tracking' => $trackNumber,
                'items' => $itemsData
            ];
            $shopId = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('etsy_config/etsy_setting/shop_name');
            if ($shipData) {
                $data = $datahelper->ApiObject()->submitTracking(
                    [
                        'params' => ['shop_id' => $shopId, 'receipt_id' => $receiptId],
                        'data' =>['tracking_code' => $trackNumber, 'carrier_name' => $shippingCarrierUsed ]
                    ]
                );

                if ($data == 'Success') {
                    $etsyModel = $this->objectManager->create('Ced\Etsy\Model\Orders')->load($incrementId, 'magento_order_id');
                    $etsyModel->setStatus('shipped');
                    $etsyModel->setShipmentData(serialize($shipData));
                    $etsyModel->save();
                }
            }
        }
        return $observer;
    }
}
