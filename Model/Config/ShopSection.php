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

namespace Ced\Etsy\Model\Config;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\Manager;
use Ced\Etsy\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class ShopSection
 *
 * @package Ced\Etsy\Model\Config
 */
class ShopSection implements ArrayInterface
{
    /**
     * @var ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var Filesystem
     */
    public $filesystem;
    /**
     * @var Manager
     */
    public $messageManager;
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var Data
     */
    public $scopeConfig;

    /**
     * @var File
     */
    public $file;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * ShopSection constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param Filesystem $filesystem
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Filesystem $filesystem,
        Manager $manager,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        File $file
    )
    {
        $this->objectManager = $objectManager;
        $this->filesystem = $filesystem;
        $this->messageManager = $manager;
        $this->helper = $helper;
        $this->scopeConfigManager = $scopeConfig;
        $this->file = $file;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $shopSections = [];
        $options[] = [
            'value' => "",
            'label' => "No Shop Section Id Available"
        ];
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/etsy/');
        $path = $folderPath . 'shopSections.json';
        $shopName = $this->scopeConfigManager->getValue('etsy_config/etsy_setting/shop_name');
        
        if ($shopName) {
            if (!$this->file->fileExists($path)) {
                try {
                    $shopSections = $this->helper->ApiObject()->findAllShopSections(['params' => ['shop_id' => $shopName]]);
                    if (isset($shopSections['results']) && !empty($shopSections['results'])) {
                        $file = fopen($path, 'w+');
                        fwrite($file, json_encode($shopSections['results']));
                        fclose($file);
                        chmod($path, 0777);
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage('Invalid Shop Name');
                    $options[] = [
                        'value' => "",
                        'label' => "Please fill Correct Shop Name"
                    ];
                }
            } else {
                $shopSections = $this->file->read($path);
                if ($shopSections != '') {
                    $shopSections = json_decode($shopSections, true);
                } else {
                    $shopSections = [];
                }
            }
            foreach ($shopSections as $value) {
                if (isset($value['shop_section_id'])) {
                   $options[] = [
                        'value' => $value['shop_section_id'],
                        'label' => $value['title']
                    ]; 
                }
                
            }
        } else {
            $options[] = [
                'value' => "",
                'label' => "Please fill the Shop Name"
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getLabel($options = [])
    {
        foreach ($this->toOptionArray() as $option) {
            $options[] =(string)$option['value'];
        }
        return $options;
    }
}
