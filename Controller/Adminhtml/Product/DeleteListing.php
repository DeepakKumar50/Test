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
use Ced\Etsy\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\Product;
use Etsy\EtsyRequestException;

/**
 * Class DeleteListing
 * @package Ced\Etsy\Controller\Adminhtml\Product
 */
class DeleteListing extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    const ADMIN_RESOURCE = 'Ced_Etsy::product';
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var Product
     */
    public $catalogCollection;

    /**
     * DeleteListing constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param Data $helper
     * @param Product $collection
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        Data $helper,
        \Magento\Catalog\Model\Product $collection
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->helper = $helper;
        $this->catalogCollection = $collection;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->catalogCollection->getCollection());
        $priceids = $collection->getAllIds();
        $error = [];
        $skus = [];
        $notice = [];
        foreach ($priceids as $pid) {
            $product = $this->catalogCollection->loadByAttribute('entity_id', $pid);
            $sku = $product->getSku();
            $listingID = $product->getEtsyListingId();
            if (!$product->getEtsyListingId()) {
                $notice[] = "SKU: " . $sku . " not uploaded on Etsy";
                continue;
            }
            try {
                $this->helper->ApiObject()->deleteListing(['params' => ['listing_id' => (int)$listingID]]);
                $product->setEtsyProductStatus('notuploaded');
                $product->setEtsyListingId('');
                $product->getResource()->save($product);
                $skus[] = $sku;
            } catch (EtsyRequestException $e) {
                $error[] = $sku." has exception ".$e->getLastResponse();
            } catch (\Exception $e) {
                $error[] = $sku." has exception ".$e->getMessage();
            }
        }
        if (!empty($notice)) {
            $this->messageManager->addNoticeMessage(implode(', ', $notice));
        }
        if (!empty($error)) {
            $this->messageManager->addErrorMessage(implode(', ', $error));
        }
        if (!empty($skus)) {
            $this->messageManager->addSuccessMessage(implode(', ', $skus) . ' delete listing on Etsy');
        }
        return $this->_redirect('etsy/product/index');
    }
}
