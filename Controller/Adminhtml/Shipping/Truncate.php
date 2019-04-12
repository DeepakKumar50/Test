<?php

/**
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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Controller\Adminhtml\Shipping;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Truncate extends \Magento\Backend\App\Action
{
    /**
     * Result Page factory
     * @var PageFactory
     */
    public $resultPageFactory;

    /** @var \Ced\Etsy\Model\ResourceModel\Shipping\CollectionFactory  */
    public $shippingCollectionFactory;

    public $shippingModel;

    /**
     * Logger
     * @var $logger \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * Data Helper
     * @var $helper
     */
    public $helper;

    public $filter;

    public function __construct(
        Context $context,
        \Ced\Etsy\Model\ResourceModel\Shippingtemplate\CollectionFactory $shippingCollectionFactory,
        \Ced\Etsy\Model\Shipping $shippingModel,
        \Psr\Log\LoggerInterface $logger,
        \Ced\Etsy\Helper\Data $helper,
        \Magento\Ui\Component\MassAction\Filter $filter,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->shippingModel = $shippingModel;
        $this->shippingCollectionFactory = $shippingCollectionFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->filter = $filter;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $feedModelIds = array();
        // delete
        $id  = $this->getRequest()->getParam('id');
        if($id) {
            $feedModelIds[] = $id;
        }
        if(count($feedModelIds) <=0) {
            // select delete
            $feedModelIds = $this->getRequest()->getParam('selected');
        }
        //echo '<pre>';print_r($feedModelIds);die;
        /*if(count($feedModelIds)<=0) {
            //select all delete
            $excluded = $this->getRequest()->getParam('excluded', false);
            if(isset($excluded) && $excluded) {
                $feedModelIds = $this->filter->getCollection($this->shippingCollectionFactory->create())->getAllIds();
            }
        }*/

        $msg = ' Shipping template Deleted Successfully';
        if(empty($feedModelIds)) {
            $msg = ' Shipping template Truncated Successfully';
            $feedModelIds = $this->shippingCollectionFactory->create()->getAllIds();
        }

        if (!empty($feedModelIds)) {
            try {
                foreach ($feedModelIds as $feedModelId) {
                    $feedModel = $this->shippingModel->load($feedModelId);
                    $feedModel->delete();
                }
                $this->messageManager->addSuccessMessage(count($feedModelIds).$msg);
            } catch (\Exception $e) {
                $this->logger->debug(" Etsy Tructate Failed : fetchShipping : " . $e->getMessage());
                return false;
            }
        }
        else {
            $this->messageManager->addErrorMessage('NO Shipping Template!');
        }
        $this->_redirect('etsy/Shipping/Index');

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