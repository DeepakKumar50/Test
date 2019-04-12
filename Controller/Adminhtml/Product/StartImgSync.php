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
use Ced\Etsy\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Etsy\EtsyRequestException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class StartImgSync
 * @package Ced\Etsy\Controller\Adminhtml\Product
 */
class StartImgSync extends Action
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
     * @var Data
     */
    public $helper;

    /**
     * @var Filesystem
     */
    public $_filesystem;

    /**
     * @var Product
     */
    public $catalogCollection;

    /**
     * StartImgSync constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     * @param Filesystem $_filesystem,
     * @param Magento\Catalog\Model\Product $collection,
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper,
        Filesystem $_filesystem,
        \Magento\Catalog\Model\Product $collection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->catalogCollection = $collection;
        $this->_filesystem = $_filesystem;
    }

    /**
     * @return string
     */
    public function execute()
    {
        $dirPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
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
                    $productImage = $product->getMediaGallery('images');
                    if (!empty($productImage)) {
                        foreach (array_reverse($productImage) as $key => $value) {
                            if (file_exists($dirPath . "catalog/product" . $value['file'])) {
                                $array = explode('.', $value['file']);
                                $type = end($array);
                                $pictureUrl = '@' . $dirPath . "catalog/product" . $value['file'] . ';type=image/' . $type;
                                
                                if (!empty($listingID) && !empty($pictureUrl)) {
                                    $etsyImage = [];
                                    $etsyImage['listing_id'] = $listingID; 
                                    $etsyImage['rank'] =(int)$key+1;
                                    $etsyImage['overwrite'] = (bool)true;
                                    $etsyImage['is_watermarked'] =(bool) false ;
                                    $args = [
                                        'params' => $etsyImage,
                                        'data' => ['image' => [$pictureUrl]]
                                    ];
                                    $result = $this->helper->ApiObject()->uploadListingImage($args);
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
        }
        if ($error) {
            $message['error'] = implode(', ', $error);
        }
        if ($success) {
            $message['success'] = implode(', ', $success)." image synced on etsy";
        }
        return $resultJson->setData($message);
    }
}
