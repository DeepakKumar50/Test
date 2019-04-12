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
use Ced\Etsy\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Country
 *
 * @package Ced\Etsy\Model\Config
 */
class Country implements ArrayInterface
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
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var Data
     */
    public $helper;
    /**
     * Country constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Filesystem $filesystem,
        Data $helper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->objectManager = $objectManager;
        $this->filesystem = $filesystem;
        $this->helper = $helper;
        $this->scopeConfigManager = $scopeConfig;
        $this->consumerkey = $this->scopeConfigManager->getValue('etsy_config/etsy_setting/consumer_key');
        $this->consumersecretkey = $this->scopeConfigManager->getValue('etsy_config/etsy_setting/consumer_secret_key');
        $this->accesstoken = $this->scopeConfigManager->getValue('etsy_config/etsy_setting/access_token');
        $this->accesstokensecret = $this->scopeConfigManager->getValue('etsy_config/etsy_setting/access_token_secret');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/etsy/');
        $path = $folderPath . 'country.json';
        if (!file_exists($path)) {
            if (empty($this->consumerkey) || empty($this->consumersecretkey)
                || empty($this->accesstoken) || empty($this->accesstokensecret)) {
                $country = "Please fill configuration section";
            } else {
                try {
                    $country = $this->helper->ApiObject()->findAllCountry(['param' => null]);
                    if (isset($country['results'])) {
                        $countries = $country['results'];
                        $file = fopen($path, 'w+');
                        fwrite($file, json_encode($countries));
                        fclose($file);
                        chmod($path, 0777);
                    } else {
                        $country = "Please fill configuration section";
                    }
                } catch (\Exception $e) {
                    $country = 'Please fill correct configuration';
                }
            }
        } else {
            $countries = file_get_contents($path);
            if ($countries != '') {
                $countries = json_decode($countries, true);
            }
        }
            
        if (!empty($countries)) {
            $options[] = [
                'value' => "",
                'label' => "Please select the country"
            ];
            foreach ($countries as $value) {
                $options[] = [
                    'value' => $value['country_id'],
                    'label' => $value['name']
                ];
            }
        } else{
            $options[] = [
                'value' => "",
                'label' => $country
            ];
        }
        return $options;
    }

    /**
     * @param $id
     * @return string
     */
    public function getContryLabel($id)
    {
        $code = '';
        $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/etsy/');
        $path = $folderPath . 'country.json';
        $countries = file_get_contents($path);
        if ($countries != '') {
            $countries = json_decode($countries, true);
        }
        foreach ($countries as $value) {
            if ($value['country_id'] == $id) {
                $code = $value['iso_country_code'];
            }
        }
        return $code;
    }
}
