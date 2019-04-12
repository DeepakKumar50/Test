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
namespace Ced\Etsy\Controller\Adminhtml\Shipping;

class Fetch extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Etsy::Etsy_Shipping_template';
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $resultRedirectFactory;
    /**
     * @var \Ced\Etsy\Helper\Order
     */
    public $orderHelper;

    /**
     * Fetch constructor.
     *
     * @param \Magento\Backend\App\Action\Context                  $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\Etsy\Helper\Order                               $orderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Ced\Etsy\Helper\ShippingTemplate $shippingTemplate
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->shippingTemplateHelper = $shippingTemplate;
        parent::__construct($context);
    }

    /**
     * fetch Latest orders from Etsy
     *
     * @return Redirect
     */
    public function execute()
    {
        $this->shippingTemplateHelper->fetchShippingTemplate();
        $result = $this->resultRedirectFactory->create();
        $result->setPath('etsy/shipping/index');
        return $result;
    }
}