<?php

namespace Ced\Etsy\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Ced\Etsy\Helper\Data;
use Ced\Etsy\Helper\Etsy;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\Product;
use Etsy\EtsyRequestException;

class Deactiveproduct extends Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
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
     * @var Etsy
     */
    public $etsy;
    /**
     * @var Product
     */
    public $catalogCollection;

    /**
     * ProductSync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param Data $helper
     * @param Etsy $etsy
     * @param Product $collection
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        Data $helper,
        Etsy $etsy,
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
        $ids = $collection->getAllIds();
        $error = [];
        $skus = [];
        $notice = [];
        foreach ($ids as $id) {
            $product = $this->catalogCollection->loadByAttribute('entity_id', $id);
            $sku = $product->getSku();
            if (!$product->getEtsyListingId()) {
                $notice[] = "SKU: " . $sku . " not uploaded on Etsy";
                continue;
            }
            try {
                $args = [
                    'params' => ['listing_id' => $product->getEtsyListingId()],
                    'data' => [ 'status' => 'inactive' ]
                ];
                $this->helper->ApiObject()->updateListing($args);
                $product->setEtsyProductStatus('inactive');
                $product->getResource()->save($product);
                $skus[] = $sku;
            } catch (EtsyRequestException $e) {
                $error[] = $sku." has exception ".$e->getLastResponse();
            } catch (\Exception $e) {
                $error[] = $sku." has exception ".$e->getMessage();
            }
        }
        if (!empty($error)) {
            $this->messageManager->addErrorMessage(implode(', ', $error));
        }
        if (!empty($notice)) {
            $this->messageManager->addNoticeMessage(implode(', ', $notice));
        }
        if (!empty($skus)) {
            $this->messageManager->addSuccessMessage(implode(', ', $skus) . ' are successfully deactive on etsy');
        }
        return $this->_redirect('etsy/product/index');
    }

}