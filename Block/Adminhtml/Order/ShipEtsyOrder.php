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

namespace Ced\Etsy\Block\Adminhtml\Order;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;
use Ced\Etsy\Model\Orders;

class ShipEtsyOrder extends AbstractOrder implements TabInterface
{
    /**
     * @var ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var Orders
     */
    public $order;
    /**
     * ShipEtsyOrder constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        Orders $orders,
        ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->order = $orders;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Retrieve Helper instance
     *
     * @return \Magento\Sales\Model\Order
     */

    public function getHelper($helper)
    {
        $helper = $this->objectManager->get("Ced\Etsy\Helper" . $helper);
        return $helper;
    }

    public function getTrackingNumber()
    {
        return $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('etsy_config/etsy_order/track_no_available');
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getModel()
    {
        $incrementId = $this->getOrder()->getIncrementId();
        $resultdata = $this->order->getCollection()->addFieldToFilter('magento_order_id', $incrementId)->getFirstItem();
        return $resultdata;
    }

    /**
     * @param $resultdata
     */

    public function setOrderResult($resultdata)
    {
        return $this->_coreRegistry->register('current_etsy_order', $resultdata);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Ship Etsy Order');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Ship Etsy Order');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $data = $this->getModel();
        if (!empty($data->getData())) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $data = $this->getModel();
        if (!empty($data->getData())) {
            return false;
        } else {
            return true;
        }
    }
}
