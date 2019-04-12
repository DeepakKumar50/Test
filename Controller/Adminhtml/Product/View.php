<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 22/5/18
 * Time: 1:17 PM
 */

namespace Ced\Etsy\Controller\Adminhtml\Products;

class View extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\ShopifyImporter\Helper\Data
     */
    public $data;

    /**
     * Json Factory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    public  $objectManager;



    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Etsy\Helper\Data $data
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->objectManager = $objectManager;
        $this->data = $data;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $url = $this->objectManager->create('Magento\Catalog\Model\Product')
            ->load($id)->getEtsyProductUrl();
        $result = [
            'fb' => 'https://www.facebook.com/sharer.php?url='.$url,
            'marketplace' => 'https://www.etsy.com/sharer.php?url='.$url,
            'twitter' => 'https://twitter.com/sharer.php?url='.$url,
            'pintrest' => 'https://www.pinterest.com/sharer.php?url='.$url,
        ];
        return $this->resultJsonFactory
            ->create()
            ->setData(stripslashes(json_encode($result)));
    }

}
