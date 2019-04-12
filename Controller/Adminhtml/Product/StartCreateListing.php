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
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductFactory;
use \Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class StartCreateListing
 * @package Ced\Etsy\Controller\Adminhtml\Product
 */
class StartCreateListing extends Action
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
     * @var \Magento\Framework\Filesystem
     */
    public $_filesystem;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $file;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * StartCreateListing constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param productFactory $productFactory
     * @param file $file
     * @param stockRegistry $stockRegistry
     * @param _filesystem $_filesystem
     * @param scopeConfig $scopeConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductFactory $productFactory,
        Filesystem\Io\File $file,
        Filesystem $_filesystem,
        StockRegistryInterface $stockRegistry,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productFactory = $productFactory;
        $this->file = $file;
        $this->stockRegistry = $stockRegistry;
        $this->_filesystem = $_filesystem;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function execute()
    {
        $message = $error = $skus = [];
        $resultJson = $this->resultJsonFactory->create();
        $key = $this->getRequest()->getParam('index');
        $folderPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('ced/etsy/');
        $path = $folderPath .'products-' . $key . '.json';
        if ($this->file->fileExists($path)) {
            $data = $this->file->read($path);
            $this->file->close();
            $items = json_decode($data, true);
            foreach ($items as $item) {
                try {
                    if (isset($item['sku']) && !empty($item['sku'])) {
                        foreach ($item['sku'] as $sku) {
                            $product = $this->productFactory->create()->loadByAttribute('sku', $sku);
                            if ($product) {
                                $stock = $this->stockRegistry->getStockItem($product->getId());
                                $stock->setIsInStock(1);
                                $stock->setQty(intval($item['quantity']));
                                $stock->save();
                                $product->setPrice($item['price']);
                                $product->getResource()->save($product);
                                $skus[] = $product->getSku(); 
                            } else {
                                $error[] = "SKU: ".$sku." not found on store.";
                            }
                        }
                    }                    
                } catch (\Exception $e) {
                    $error[] = $e->getMessage();
                }
            }
            $activeProd = $this->scopeConfig->getValue('etsy_config/etsy_setting/active_products');
            $message['check'] = (int)$activeProd > 100 * $key ? "continue" : '';
            $message['success'] = "Magento SKU: " . implode(', ', $skus) . " Synced in Magento Successfully";
        } else {
            $message['check'] = "";
            $message['success'] = "product data file not exists ";
        }
        if ($error) {
            $message['error'] = implode(', ', $error);
        }
        return $resultJson->setData($message);
    }
}
