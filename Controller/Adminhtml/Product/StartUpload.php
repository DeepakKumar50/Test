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
use Magento\Framework\View\Result\PageFactory;
use Ced\Etsy\Helper\Etsy;
use Ced\Etsy\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductFactory;
use \Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Etsy\EtsyRequestException;

/**
 * Class StartUpload
 * @package Ced\Etsy\Controller\Adminhtml\Product
 */
class StartUpload extends Action
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
     * @var ProductFactory
     */
    public $productFactory;

    /**
     * @var Etsy
     */
    public $etsy;
    /**
     * @var \Magento\Framework\Filesystem
     */
    public $_filesystem;

    /**
     * @var Data
     */
    public $helper;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * StartUpload constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Etsy $etsy
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        ProductFactory $productFactory,
        Etsy $etsy,
        Data $helper,
        Filesystem $_filesystem,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productFactory = $productFactory;
        $this->etsy = $etsy;
        $this->helper = $helper;
        $this->_filesystem = $_filesystem;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function execute()
    {
        $childImages=[];
        $inventry_data=[];
        $dirPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $resultJson = $this->resultJsonFactory->create();
        $message = $error = $success = [];
        $shopLanguage = $this->scopeConfig->getValue('etsy_config/etsy_setting/store_language');
        $key = $this->getRequest()->getParam('index');
        $totalChunk = $this->_session->getProductChunks();
        $index = $key + 1;
        if (count($totalChunk) <= $index) {
            $this->_session->unsProductChunks();
        }
        if (isset($totalChunk[$key])) {
            $ids = $totalChunk[$key];
            foreach ($ids as $id) {
                $product = $this->productFactory->create()->loadByAttribute('entity_id', $id);
                $sku = $product->getSku();
                try {
                    $finaldata = $this->etsy->prepareData($product);
                    if ($finaldata['type'] == 'success') {
                        if(isset($finaldata['data']['configurable'])) {
                            $inventry_data=$finaldata['data']['configurable'];
                            $childImages=$finaldata['data']['childImages'];
                            unset($finaldata['data']['configurable']);
                            unset($finaldata['data']['childImages']);
                        }
                        unset($finaldata['data']['sku']);
                       // var_dump($finaldata['data']);
                         

                        if ($shopLanguage == 'en') {
                            $response = $this->helper->ApiObject()->createListing(['data' => $finaldata['data']]);
                        } else {
                            $response = $this->helper->ApiObject()->createListing(['data' => $finaldata['data'],
                                'params'=>['language'=>$shopLanguage]]);
                        }
                    

                        if (isset($response['results'])) {
                            $listingID = $response['results'][0]['listing_id'];
                            $args = [
                                'params' => ['listing_id' => $listingID],
                                'data' => ['sku' => $sku, 'renew' => true]
                            ];
                            $this->helper->ApiObject()->updateListing($args);
                            //variant Product upload
                            if (!empty($inventry_data)) {
                                $response = $this->helper->ApiObject()->updateInventory(['data' => $inventry_data,
                                    'params' => ['listing_id' => $listingID]]);
                            }
                            $product->setEtsyProductStatus('uploaded');
                            $product->setEtsyListingId($listingID);
                            $product->getResource()->save($product);
                            //image upload
                            $product =$this->productFactory->create()->load($id);
                            $productImage = $product->getMediaGallery('images');
                            if (!empty($productImage)) {
                                foreach (array_reverse($productImage) as $value) {
                                    if (file_exists($value['file']))
                                        if (!isset($value['file'])) {
                                            continue;
                                        }
                                    $array = explode('.', $dirPath . "catalog/product" . $value['file']);
                                    $type = end($array);
                                    $pictureUrl = '@' . $dirPath . "catalog/product" . $value['file'] . ';type=image/' . $type;
                                    if (!empty($listingID) && !empty($pictureUrl)) {
                                        $args = [
                                            'params' => ['listing_id' => $listingID],
                                            'data' => ['image' => [$pictureUrl]]
                                        ];
                                        $this->helper->ApiObject()->uploadListingImage($args);
                                    }
                                }
                            }
                            if (!empty($childImages)) {
                                //variant Product Image upload
                                foreach (array_reverse($childImages) as $value) {

                                    if (file_exists($value['file']))
                                        if (!isset($value['file'])) {
                                            continue;
                                        }
                                    $array = explode('.', $dirPath . "catalog/product" . $value['file']);
                                    $type = end($array);
                                    $pictureUrl = '@' . $dirPath . "catalog/product" . $value['file'] . ';type=image/' . $type;
                                    if (!empty($listingID) && !empty($pictureUrl)) {
                                        $args = [
                                            'params' => ['listing_id' => $listingID],
                                            'data' => ['image' => [$pictureUrl]]
                                        ];
                                        $this->helper->ApiObject()->uploadListingImage($args);
                                    }
                                }
                            }
                            $success[] = $sku;
                        }
                    } else {
                        $product->setEtsyProductStatus('invalid');
                        $product->getResource()->save($product);
                        $error[] = $sku." has error ".$finaldata['data'];
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
            $message['success'] = implode(', ', $success)." uplaoded on etsy";
        }
        return $resultJson->setData($message);
    }
}
