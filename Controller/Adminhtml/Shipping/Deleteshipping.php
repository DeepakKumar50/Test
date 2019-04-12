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

namespace Ced\Etsy\Controller\Adminhtml\Shipping;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Deleteshipping  extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Etsy::shipping_template';
    /**
     * ResultPageFactory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Ced\Etsy\Helper\ShippingTemplate $shippingTemplate,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->shippingTemplateHelper = $shippingTemplate;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $params=$this->getRequest()->getParams();
        $this->shippingTemplateHelper->deleteShippingTemplate($params);
        $result = $this->resultRedirectFactory->create();
        $result->setPath('etsy/shipping/index');
        return $result;
    }
}
