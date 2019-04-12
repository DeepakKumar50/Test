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
use Magento\Framework\App\Filesystem\DirectoryList;
use Etsy\EtsyRequestException;

/**
 * Class ProductImage
 * @package Ced\Etsy\Controller\Adminhtml\Product
 */
class ProductImage extends Action
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
     * @var Product
     */
    public $catalogCollection;
    /**
     * @var
     */
    protected $_filesystem;

    /**
     * ProductImage constructor.
     * @param \Magento\Framework\Filesystem $_filesystem
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param Data $helper
     * @param Product $collection
     */
    public function __construct(
        \Magento\Framework\Filesystem $_filesystem,
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
        $this->_filesystem = $_filesystem;
    }


    public function execute()
    {
        $dirPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $collection = $this->filter->getCollection($this->catalogCollection->getCollection());
        $imageids = $collection->getAllIds();
        $error = [];
        $skus = [];
        $notice = [];
        foreach ($imageids as $imgid) {
            $product = $this->catalogCollection->loadByAttribute('entity_id', $imgid);
            $sku = $product->getSku();
            $listingID = $product->getEtsyListingId();
            if (!$product->getEtsyListingId()) {
                $notice[] = "SKU: " . $sku . " not uploaded on Etsy";
                continue;
            }
            try {
                $product = $this->catalogCollection->load($imgid);
                $productImage = $product->getMediaGallery('images');
                if (!empty($productImage)) {
                    foreach (array_reverse($productImage) as $key => $value) {
                        if (file_exists($dirPath . "catalog/product" . $value['file'])) {
                            $array = explode('.', $value['file']);
                            $type = end($array);
                            $pictureUrl = '@' . $dirPath . "catalog/product" . $value['file'] . ';type=image/' . $type;
                            
                            if (!empty($listingID) && !empty($pictureUrl)) {
                                $etsy_image = array();
                                $etsy_image['listing_id'] = $listingID; 
                                $etsy_image['rank'] =(int)$key+1;
                                $etsy_image['overwrite'] = (bool)true;
                                $etsy_image['is_watermarked'] =(bool) false ;
                                $args = [
                                    'params' => $etsy_image,
                                    'data' => ['image' => [$pictureUrl]]
                                ];
                                $this->helper->ApiObject()->uploadListingImage($args);
                            }
                        } else {
                            $error[] = $sku." has no image to upload";
                        }
                    }
                    $skus[] = $sku;
                }
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
            $this->messageManager->addSuccessMessage(implode(', ', $skus) . ' images uploaded successfully');
        }
        return $this->_redirect('etsy/product/index');
    }
}
