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
 * @package     Ced_CsGroup
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Etsy\Controller\Adminhtml\Profile;
use Magento\Framework\View\Result\PageFactory;
use Ced\Etsy\Helper\Data;

class Updatecategoryattribute extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->helper = $helper;
    }

    /**
     * Vendor grid for AJAX request
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $c_id = $this->getRequest()->getParam('c_id');
        $response = $this->helper->ApiObject()->getTaxonomyNodeProperties([ 'params' => ['taxonomy_id'=>$c_id] ]);
        if(isset($response['results'])){
            $result = $this->resultPageFactory->create(true)->getLayout()->createBlock('Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab\Attribute\Configattribute')->setAttributeResponse($response)->toHtml();
        }
        $this->getResponse()->setBody($result);
    }

}
