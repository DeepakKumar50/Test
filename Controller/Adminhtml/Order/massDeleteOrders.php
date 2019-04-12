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

class massDeleteOrders extends \Magento\Backend\App\Action
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
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->filter = $filter;
        $this->OrdersCollectionFactory = $OrdersCollectionFactory;
        $this->OrdersFactory = $OrdersFactory;
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
        if($dataPost) {
            $OrdersModelIds = $this->filter->getCollection($this->OrdersCollectionFactory->create())->getAllIds();
        } else {
            $OrdersModelIds[] = $this->getRequest()->getParam('id');
        }
        if(isset($OrdersModelIds)) {
            try {
                foreach ($OrdersModelIds as $OrdersModelId) {
                    $this->OrdersFactory->create()
                        ->load($OrdersModelId)
                        ->delete();
                }
                $count = count($OrdersModelIds);
                if($count) {
                    $this->messageManager->addSuccess(
                        __($count .' Order(s) Delete Succesfully')
                    );
                }
                else {
                    $this->messageManager->addErrorMessage(__(' Order Not Deleted '));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__(''.$e->getMessage()));
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