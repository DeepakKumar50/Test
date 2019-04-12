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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Listing extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Etsy::product';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Ced\Etsy\Helper\Data
     */
    public $helper;
    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * Listing constructor.
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\Etsy\Helper\Data $data,
        Filesystem $filesystem,
        Filesystem\Io\File $file
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->helper = $data;
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/etsy/');
        $shopName = $this->scopeConfig->getValue('etsy_config/etsy_setting/shop_name');
        $activeProd = $this->scopeConfig->getValue('etsy_config/etsy_setting/active_products');
        if (!$shopName || $activeProd == 0) {
            $this->messageManager->addErrorMessage("Shop name is missing OR active product count is set to '0' in Etsy Configuration");
            return $this->_redirect('etsy/product/thirdpartylisting');
        }
        $pages = (int)$activeProd/100;
        try {
            $success = false;
            for ($i=1; $i <= ceil($pages); $i++) {
                $results = $this->helper->ApiObject()->findAllShopListingsActive(
                    [
                        'params' => [
                            'shop_id' => $shopName,
                            'limit' => 100,
                            'page' => $i
                        ]
                    ]
                );
                if (!file_exists($folderPath)) {
                    $this->file->mkdir($folderPath, 0777, true);
                }
                $path = $folderPath . 'products-'.$i.'.json';
                if (isset($results['results']) && !empty($results['results'])) {
                    $success =  true;
                    $file = fopen($path, 'w+');
                    fwrite($file, json_encode($results['results']));
                    fclose($file);
                    chmod($path, 0777);
                }
            }
            if ($success) {
                $this->messageManager->addSuccessMessage("Listing Imported Successfully");
            } else {
                $this->messageManager->addErrorMessage("Active Listing on Etsy is null");
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->_redirect('etsy/product/thirdpartylisting');
    }
}
