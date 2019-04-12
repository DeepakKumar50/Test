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

namespace Ced\Etsy\Controller\Adminhtml\Order;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Ced\Etsy\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Ship extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Etsy::Etsy_orders';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfigManager;
    /**
     * @var Data
     */
    public $data;
    /**
     * @var \Ced\Etsy\Helper\Order
     */
    public $order;

    /**
     * Ship constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Data $data,
        ScopeConfigInterface $scopeConfig,
        \Ced\Etsy\Helper\Order $order
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfigManager = $scopeConfig;
        $this->data = $data;
        $this->order = $order;
    }

    /**
     * @return string
     */

    public function execute()
    {
        // collect ship data
        $postData = $this->getRequest()->getPost();
        $shipTodatetime = strtotime($postData['ship_todate']);
        $id = $postData['id'];
        $incrementOrderId = $postData['incrementid'];
        $shippingCarrierUsed = $postData['carrier'];
        $trackNumber = $postData['tracking'];
        $receiptId = $postData['receipt_id'];
        $itemsData = json_decode($postData['items'], true);
        if (empty($itemsData)) {
            $this->getResponse()->setBody("You have no item in your Order.");
            return;
        }
        $shipData = [
            'ship_todate' => $shipTodatetime,
            'carrier' => $shippingCarrierUsed,
            'tracking' => $trackNumber,
            'items' => $itemsData
        ];
        $shipQtyForOrder = $cancelQtyForOrder = [];
        foreach ($itemsData as $value) {
            if ($value['ship_qty'] > 0) {
                $shipQtyForOrder[$value['sku']] = $value['ship_qty'];
            }
            if ($value['cancel_quantity'] > 0) {
                $cancelQtyForOrder[$value['sku']] = $value['cancel_quantity'];
            }
        }
        $etsyModel = $this->_objectManager->create('Ced\Etsy\Model\Orders')->load($id);
        $shopId = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('etsy_config/etsy_setting/shop_name');
        $data = $this->data->ApiObject()->submitTracking(
            [
                'params' => ['shop_id' => $shopId, 'receipt_id' => $receiptId],
                'data' =>['tracking_code' => $trackNumber, 'carrier_name' => $shippingCarrierUsed ]
            ]
        );
        $order = $this->_objectManager->get(
            'Magento\Sales\Model\Order'
        )->loadByIncrementId($incrementOrderId);
        $itemQty = [];
        $itemQtytoCancel = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $shipSku = $item->getSku();
            if (isset($shipQtyForOrder[$shipSku])) {
                $itemQty[$item->getId()] = $shipQtyForOrder[$shipSku];
            }
            if (isset($cancelQtyForOrder[$shipSku])) {
                $itemQtytoCancel[$item->getId()] = $cancelQtyForOrder[$shipSku];
            }
        }
        if (!empty($itemQty)) {
            if ($order->canShip()) {
                $this->order->generateShipment($order, $itemQty);
            }
        }
        if (!empty($itemQtytoCancel)) {
            $this->data->generateCreditMemo($order, $itemQtytoCancel);
        }
        $etsyModel->setStatus('shipped');
        $etsyModel->setShipmentData(serialize($shipData));
        $etsyModel->save();
        $this->messageManager->addSuccessMessage('Your Etsy Order ' . $incrementOrderId . ' has been Completed');
        $this->getResponse()->setBody("Success");
    }
}
