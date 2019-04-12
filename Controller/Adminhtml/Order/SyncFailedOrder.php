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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class SyncFailedOrder extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    public $helper;


    public $filter;

    public $OrdersCollectionFactory;


    public $OrdersFactory;

    /**
     * FailedOrders constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Ced\Etsy\Helper\Order $helper
     */
    public function __construct(
        \Ced\Etsy\Model\ResourceModel\Orders\CollectionFactory $OrdersCollectionFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Etsy\Model\OrdersFactory $OrdersFactory,
        \Ced\Etsy\Helper\Order $helper,
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->filter = $filter;
        $this->OrdersCollectionFactory = $OrdersCollectionFactory;
        $this->OrdersFactory = $OrdersFactory;
        $this->helper = $helper;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $dataPost = $this->getRequest()->getParam('filters');
        $successPOS = array();
        $errorPOS = array();
        $message = '';
        if($dataPost) {
            $OrdersModelIds = $this->filter->getCollection($this->OrdersCollectionFactory->create())->getAllIds();
        } else {
            $OrdersModelIds[] = $this->getRequest()->getParam('id');
        }

        if(isset($OrdersModelIds)) {
            try {
                foreach ($OrdersModelIds as $OrdersModelId) {
                    $orderModel = $this->OrdersFactory->create()->load($OrdersModelId);
                    if(!$orderModel->getMagentoOrderId() || $orderModel->getMagentoOrderId() =='N/A') {
                       $resultData = $this->helper->fetchLatestOrders($orderModel->getPurchaseOrderId());
                        if(isset($resultData['success'])) {
                            $successPOS[] = $resultData['message'];
                        }
                        else if(isset($resultData['error'])) {
                            $errorPOS[] = $resultData['message'];
                        }
                        else {
                            $message = $resultData;
                        }
                    }
                }
                if(count($successPOS ) > 0) {
                    $message = 'The following Purchase Order Id\'s Sync Successfully - '.implode(',' , $successPOS);
                    $this->messageManager->addSuccessMessage(
                        __($message)
                    );
                } else if(count($errorPOS ) > 0) {
                    $message = 'The following Purchase Order Id\'s failed to Sync - '.implode(',' , $errorPOS);
                    $this->messageManager->addErrorMessage(
                        __($message)
                    );
                }
                else {
                    $this->messageManager->addErrorMessage(
                        __($message='Failed to Sync')
                    );
                }

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Failed To sync '.$e->getMessage()));
            }
        }
        else {
            $this->messageManager->addErrorMessage(__('Please Select Order '));
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Etsy::Etsy');
    }
}