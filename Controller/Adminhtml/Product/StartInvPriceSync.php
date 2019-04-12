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
use Magento\Framework\View\Result\PageFactory;
use Ced\Etsy\Helper\Etsy;
use Ced\Etsy\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\Controller\Result\JsonFactory;
use Etsy\EtsyRequestException;

/**
 * Class StartInvPriceSync
 * @package Ced\Etsy\Controller\Adminhtml\Product
 */
class StartInvPriceSync extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Etsy::product';
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var Etsy
     */
    public $etsy;

    /**
     * @var Data
     */
    public $helper;
    /**
     * @var Product
     */
    public $catalogCollection;
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    public $stockItem;

    /**
     * StartInvPriceSync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Etsy $etsy
     * @param Data $helper
     * @param Product $collection
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Etsy $etsy,
        Data $helper,
        \Magento\Catalog\Model\Product $collection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->etsy = $etsy;
        $this->helper = $helper;
        $this->catalogCollection = $collection;
    }

    /**
     * @return string
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $message = $error = $success = [];
        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getProductChunks();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsProductChunks();
        }
        if (isset($totalChunk[$key])) {
            $ids = $totalChunk[$key];
            foreach ($ids as $id) {
                try {
                    $product = $this->catalogCollection->loadByAttribute('entity_id', $id);
                    $sku = $product->getSku();
                    $listingID = $product->getEtsyListingId();
                    if (!$product->getEtsyListingId()) {
                        $notice[] = "SKU: " . $sku . " not uploaded on Etsy";
                        continue;
                    }
                    $qty = $this->etsy->getEtsyInventory($product);
                    $price = $this->etsy->getEtsyPrice($product);
                    if ((int)$qty > 0) {
                        $data = [
                                'quantity' => (int)$qty,
                                'price' => floatval($price),
                                'state' => 'active'
                            ];
                    } else {
                        $data = [ 'state' => 'inactive' ];
                    }
                    $this->helper->ApiObject()->updateListing(
                        [
                            'params' => ['listing_id' => (int)$listingID],
                            'data' => $data
                        ]
                    );
                    $success[] = $sku;
                } catch (EtsyRequestException $e) {
                    $error[] = $sku." has exception ".$e->getLastResponse();
                } catch (\Exception $e) {
                    $error[] = $sku." has exception ".$e->getMessage();
                }
            }
        }
        if ($error) {
            $message['error'] = implode(', ', $error);
        }
        if ($success) {
            $message['success'] = implode(', ', $success)." uplaoded on etsy";
        }
        return $resultJson->setData($message);
    }
}
