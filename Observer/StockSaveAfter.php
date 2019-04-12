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

namespace Ced\Etsy\Observer;

class StockSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Ced\Etsy\Helper\Data
     */
    public $helper;
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    public $stockItem;

    /**
     * ProductSaveAfter constructor.
     * @param \Ced\Etsy\Helper\Data $helper
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Ced\Etsy\Helper\Data $helper
    ) {
        $this->stockItem = $stockItem;
        $this->helper = $helper;
    }
    
    /*
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $productId = $observer->getEvent()->getItem()->getProductId();
            $product = $observer->getEvent()->getProduct();
            if ($product && $product->getId() && $product->getEtsyListingId()) {
                $qty = $this->stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                if ((int)$qty > 0) {
                    $data = [
                            'quantity' => (int)$qty,
                            'state' => 'active'
                        ];
                } else {
                    $data = [ 'state' => 'inactive' ];
                }
                $this->helper->ApiObject()->updateListing(
                    [
                        'params' => ['listing_id' => (int)$product->getEtsyListingId()],
                        'data' => $data
                    ]
                );
                return true;
            }
        } catch (\Exception $exception) {
            return true;
        }
    }
}
