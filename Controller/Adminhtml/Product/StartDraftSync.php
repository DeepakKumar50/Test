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

namespace Ced\Etsy\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductFactory;

/**
 * Class StartDraftSync
 * @package Ced\Etsy\Controller\Adminhtml\Product
 */
class StartDraftSync extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Etsy::product';
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var ProductFactory
     */
    public $productFactory;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Ced\Etsy\Helper\Data
     */
    public $helper;

    /**
     * StartActiveSync constructor.
     * @param  Context $context
     * @param  JsonFactory $resultJsonFactory
     * @param  ProductFactory $productFactory
     * @param  ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductFactory $productFactory,
        \Ced\Etsy\Helper\Data $data,
        ScopeConfigInterface $scopeConfig
    ) 
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productFactory = $productFactory;
        $this->helper = $data;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function execute()
    {
        $message = $error = $skus = [];
        $resultJson = $this->resultJsonFactory->create();
        $page = $this->getRequest()->getParam('index');
        $shopName = $this->scopeConfig->getValue('etsy_config/etsy_setting/shop_name');
        if (!$shopName) {
            $this->messageManager->addErrorMessage("please enter the shop name in configuration");
            return $this->_redirect('etsy/product/index');
        }
        try {
            $results = $this->helper->ApiObject()->findAllShopListingsDraft(
                [
                    'params' => [
                        'shop_id' => $shopName,
                        'limit' => 50,
                        'page' => $page
                    ]
                ]
            );
        } catch (\Exception $e) {
            $message['check'] = "";
            $message['error'] = $e->getMessage();
        }
        if (isset($results['results']) && !empty($results['results'])) {
            foreach ($results['results'] as $item) {
                try {
                    if (isset($item['sku']) && !empty($item['sku'])) {
                        foreach ($item['sku'] as $sku) {
                            $product = $this->productFactory->create()->loadByAttribute('sku', $sku);
                            if ($product) {
                                $product->setEtsyStatus($item['state']);
                                $product->setEtsyListingId($item['listing_id']);
                                $product->getResource()->save($product);
                                $skus[] = $product->getSku(); 
                            } else {
                                $error[] = "SKU: ".$sku." not found on store.";
                            }
                        }
                    }                    
                } catch (\Exception $e) {
                    $error[] = "Exception found in: listing Id: ". $item['listing_id']. $e->getMessage();
                }
            }
            $message['check'] = 'continue';
            $message['success'] = "Magento SKU: " . implode(', ', $skus) . " Synced in Magento Successfully";
        } else {
            $message['check'] = "";
            $message['success'] = "products not available ";
        }

        if ($error) {
            $message['check'] = "continue";
            $message['error'] = implode(', ', $error);
        }
        return $resultJson->setData($message);
    }
}
