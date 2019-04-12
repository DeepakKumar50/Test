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
namespace Ced\Etsy\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Backend\Model\Session;
use Magento\Framework\Filesystem;

/**
 * Class Etsy
 * @package Ced\Etsy\Helper
 */
class Etsy extends AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var Session
     */
    public $adminSession;
    /**
     * @var Manager
     */
    public $messageManager;
    /**
     * DirectoryList
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    public $directoryList;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;
    /**
     * @var Curl
     */
    public $_resource;
    /**
     * @var string
     */
    public $consumerkey;
    /**
     * @var string
     */
    public $consumersecretkey;
    /**
     * @var string
     */
    public $tokensecret;
    /**
     * @var string
     */
    public $token;
    /**
     * @var string
     */
    public $accesstoken;
    /**
     * @var string
     */
    public $accesstokensecret;
    /**
     * @var string
     */
    public $country;
    /**
     * @var string
     */
    public $storeid;
    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * Etsy constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Manager $manager
     * @param DirectoryList $directoryList
     * @param Data $json
     * @param Curl $curl
     * @param Session $session
     * @param Filesystem $filesystem ,
     */
    public function __construct(
        Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        ObjectManagerInterface $objectManager,
        Manager $manager,
        DirectoryList $directoryList,
        \Magento\Framework\Json\Helper\Data $json,
        Curl $curl,
        Session $session,
        Filesystem $filesystem
    )
    {
        $this->objectManager = $objectManager;
        $this->_resource = $curl;
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
        $this->messageManager = $manager;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->adminSession = $session;
        $this->filesystem = $filesystem;
        $this->consumerkey = $this->scopeConfig->getValue('etsy_config/etsy_setting/consumer_key');
        $this->consumersecretkey = $this->scopeConfig->getValue('etsy_config/etsy_setting/consumer_secret_key');
        $this->accesstoken = $this->scopeConfig->getValue('etsy_config/etsy_setting/access_token');
        $this->accesstokensecret = $this->scopeConfig->getValue('etsy_config/etsy_setting/access_token_secret');
        $this->country = $this->scopeConfig->getValue('etsy_config/etsy_setting/country');
        $this->storeid = $this->scopeConfig->getValue('etsy_config/etsy_setting/storeid');
    }

    public function prepareData($product)
    {
        try {
            $itemData3=[];
            $processingDaysMin = $processingDaysMax = '';
            $profileId = $this->objectManager->get('Ced\Etsy\Model\Profileproducts')->loadByField('product_id',
                $product->getEntityId());
            $profileData = $this->objectManager->get('Ced\Etsy\Model\Profile')->load($profileId->getProfileId());

            $catJson = $profileData->getProfileCategory();
            $primarycatId = "";
            if ($catJson) {
                $catArray = array_reverse(json_decode($catJson, true));
                foreach ($catArray as $value) {
                    if ($value != "") {
                        $primarycatId = $value;
                        break;
                    }
                }
            }
            $primarycatArray = explode('.', $primarycatId);
            $primarycatId = $primarycatArray[0];
            $reqOptAttr = $profileData->getProfileReqOptAttribute();
            $itemData1 = $this->reqOptAttributeData($product, json_decode($reqOptAttr, true));
            if (isset($itemData1['type'])) {
                $content = [
                    'type' => 'error',
                    'data' => $itemData1['data']
                ];
                return $content;
            }
            if($product->getTypeId()=='configurable'){
                $returnValue=$this->prepareVariatinData($product,$profileData);
                $itemData3['childImages']=$returnValue['childImages'];
                $itemData3['configurable']=$returnValue['childProducts'];
            }
            $tagString = $profileData->getTags();
            $tagArray = explode(',', $tagString);
            if (count($tagArray) > 13) {
                $temTags = array_chunk($tagArray, 13);
                $tags = implode(',', $temTags[0]);
            } else {
                $tags = $tagString;
            }
            $tags = $tags != '' ? [$tags] : [];
            $itemData2 = [
                'taxonomy_id' => (int)$primarycatId,
                'tags' => $tags,
                'recipient' => $profileData->getRecipient(),
                'occasion' => $profileData->getOccasion(),
            ];
            $itemData = array_merge_recursive($itemData1,$itemData2,$itemData3);
            if (!isset($itemData['shipping_template_id'])) {
                $itemData['shipping_template_id'] = (int)$this->scopeConfig->getValue('etsy_config/product_setting/shipping_template');
            }
            if (!isset($itemData['shop_section_id'])) {
                $shopSectionId= (int)$this->scopeConfig->getValue('etsy_config/product_setting/shop_section_id');
                if ($shopSectionId) {
                    $itemData['shop_section_id'] = $shopSectionId;
                 } 
            }
            $folderPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/etsy/');
            $path = $folderPath . 'ShippingTemplate.json';

            if (file_exists($path)) {
                $shippingTemplates = file_get_contents($path);
            
                if ($shippingTemplates != '') {
                    $shippingTemplates = json_decode($shippingTemplates, true);
                }
                foreach ($shippingTemplates as $value) {
                    if ($value['shipping_template_id'] == $itemData['shipping_template_id']) {
                        $processingDaysMin = $value['min_processing_days'];
                        $processingDaysMax = $value['min_processing_days'];
                        break;
                    }
                }
            }
            if ($processingDaysMin) {
                $itemData['processing_min'] = $processingDaysMin;
            }
            if ($processingDaysMax) {
                $itemData['processing_max'] = $processingDaysMax;
            }

            if (!isset($itemData['state'])) {
                $itemData['state'] = $this->scopeConfig->getValue('etsy_config/product_setting/state');
            }
            if (!isset($itemData['when_made'])) {
                $itemData['when_made'] = $this->scopeConfig->getValue('etsy_config/product_setting/when_made');
            }
            if (!isset($itemData['who_made'])) {
                $itemData['who_made'] = $this->scopeConfig->getValue('etsy_config/product_setting/who_made');
            }
            /*if (!isset($itemData['is_customizable'])) {
               $itemData['is_customizable'] = (boolean)0;
            }*/
            if (!isset($itemData['non_taxable'])) {
                $itemData['non_taxable'] = true;
            }
            if (!isset($itemData['is_supply'])) {
                $itemData['is_supply'] = true;
            }
           if (!$product->getEtsyLisitingId()) {
                $itemData['quantity'] = $this->getEtsyInventory($product);
                if(  $itemData['quantity'] ==0 && $product->getTypeId()=='configurable'){
                    $itemData['quantity']=1;
                }
                $itemData['price'] = $this->getEtsyPrice($product);
            } else {

            }
            $content = [
                'type' => 'success',
                'data' => $itemData
            ];
        } catch (\Exception $e) {
            $content = [
                'type' => 'error',
                'data' => $e->getMessage()
            ];
        }
    
        return $content;
    }

    /**
     * @param $product
     * @param $profileData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareVariatinData($product,$profileData)
    {
        $childImages = [];
        $config_attribute = json_decode($profileData->getconfig_attributes(),true);
        $_children = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($config_attribute as $key => $values) {
            $magentoAttributeCode=$values['magento_attribute_code'];
            $optionMapping=json_decode($values['option_mapping'],1);
            $childItem=[];
            $error = false;
            $msg = "";
            foreach ($_children as $child) {
                $images = $child->getMediaGalleryImages();
                foreach ($images as $image) {
                    $childImages[]=$image;
                }
                $attributeDetails = $this->eavConfig->getAttribute("catalog_product", $magentoAttributeCode);
                if ($attributeDetails->getFrontendInput() == 'select') {
                    $childvalue['config_field'] = $child->getAttributeText($magentoAttributeCode);

                } else {
                    $childvalue['config_field'] = $child->getData($magentoAttributeCode);
                }
                if (empty($childvalue['config_field'])) {
                    $error = true;
                    $msg = $magentoAttributeCode . "is empty";
                } else {

                    if (isset($optionMapping[$childvalue['config_field']]) || ($values['etsy_property_id']=="514") || ($values['etsy_property_id']=="513") ) {
                        if(isset($optionMapping[$childvalue['config_field']])){
                            $options = explode(',', $values['options']);
                            foreach ($options as $option) {
                                $optionsvalues[explode(':', $option)[0]] = explode(':', $option)[1];
                                $OptionValue=[ $optionMapping[$childvalue['config_field'] ] ];
                            }
                        }elseif( $childvalue['config_field']){
                            $OptionValue=$childvalue['config_field'];
                        }

                            $varientarray[] = [
                                'property_id' => (int)$values['etsy_property_id'],
                                "is_available"=> true,
                                'value' => (string)ucfirst($values['etsy_attribute_name']),
                                'price'=>(float)$this->getEtsyPrice($child),
                                'quantity' => (int)$this->getEtsyInventory($child)
                            ];
                            $updatearray[] = [
                                'sku' => $child->getSku(),
                                'property_values'=> [
                                    [
                                        'property_id'=>  (int)$values['etsy_property_id'],
                                        'property_name'=> $values['etsy_attribute_name'],
                                        'values'=>$OptionValue
                                    ]
                                ],
                                'offerings'=> [
                                    [
                                        'price'=>(float)$this->getEtsyPrice($child),
                                        'quantity' => (int)$this->getEtsyInventory($child),
                                        'is_enabled'=> 1,
                                    ]
                                ]
                            ];
                         } else {

                        $error = true;
                        $msg = $magentoAttributeCode . "please select the option value";
                    }
                }

            }
            $price_on_property[] = $values['etsy_property_id'];
            $quantity_on_property[] = $values['etsy_property_id'];
            $sky_on_property[] = $values['etsy_property_id'];
        }
        $inventry_data['products'] = ['json' => json_encode($updatearray)];
        $inventry_data['price_on_property'] = array_unique($price_on_property);
        $inventry_data['quantity_on_property'] = array_unique($quantity_on_property);
        $inventry_data["sku_on_property"] = array_unique($sky_on_property);
        $returnValue = ['childImages' => $childImages, 'childProducts' => $inventry_data ];
        return $returnValue;
    }

    /**
     * @param $product
     * @param $reqOptAttr
     * @return array
     */
    public function reqOptAttributeData($product, $reqOptAttr)
    {
        $item = [];
        $error = false;
        $msg = "";
        try {
            foreach ($reqOptAttr['required_attributes'] as $value) {
                switch ($value['etsy_attribute_name']) {
                    case 'name':
                        $item['title'] = $value['magento_attribute_code'] =='default' ? $value['default'] : $product->getData($value['magento_attribute_code']);
                        if (empty($item['title'])) {
                            $error = true;
                            $msg = "title is missing";
                        } else {
                            $item['title'] = substr($item['title'], 0, 140);
                        }
                        break;
                    case 'sku':
                        $item['sku'] = $product->getData($value['magento_attribute_code']);
                        if (empty($item['sku'])) {
                            $error = true;
                            $msg = "SKU is missing";
                        }
                        break;
                    case 'description':
                        $item['description'] = $value['magento_attribute_code'] =='default' ? $this->getDescriptionTemplate($product, $value['default']) : strip_tags($product->getData($value['magento_attribute_code']));
                        if (empty($item['description'])) {
                            $error = true;
                            $msg = "Description is missing";
                        }
                        break;
                    default:
                        break;
                }
                if ($error) {
                    break;
                }
            }
            if (!empty($reqOptAttr['optional_attributes'])) {
                foreach ($reqOptAttr['optional_attributes'] as $optAttr) {
                    switch ($optAttr['etsy_attribute_name']) {
                        case 'when_made':
                            $item['when_made'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($value['magento_attribute_code']);
                            break;
                        case 'who_made':
                            $item['who_made'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($value['magento_attribute_code']);
                            break;
                        case 'shipping_template_id':
                            $item['shipping_template_id'] = $optAttr['magento_attribute_code'] =='default' ? (int)$optAttr['default'] : (int)$product->getData($value['magento_attribute_code']);
                            break;
                        case 'shop_section_id':
                            $item['shop_section_id'] = $optAttr['magento_attribute_code'] =='default' ? (int)$optAttr['default'] : (int)$product->getData($value['magento_attribute_code']);
                            break;
                        case 'state':
                            $item['state'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($value['magento_attribute_code']);
                            break;
                        case 'materials':
                            $item['materials'] = $optAttr['magento_attribute_code'] =='default' ? $optAttr['default'] : $product->getData($value['magento_attribute_code']);
                            break;
                        case 'is_customizable':
                            $item['is_customizable'] = $optAttr['magento_attribute_code'] =='default' ? boolval($optAttr['default']) : $product->getData($value['magento_attribute_code']);
                            break;
                        case 'non_taxable':
                            $item['non_taxable'] = $optAttr['magento_attribute_code'] =='default' ? boolval($optAttr['default']) : $product->getData($value['magento_attribute_code']);
                            break;
                        case 'is_supply':
                            $item['is_supply'] = $optAttr['magento_attribute_code'] =='default' ? boolval($optAttr['default']) : $product->getData($value['magento_attribute_code']);
                            break;
                        default:
                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            $error = true;
            $msg = $e->getMessage();
        }
        if ($error) {
            $item['type'] = "error";
            $item['data'] = $msg;
        }
        return $item;
    }

    public function getDescriptionTemplate($product, $value=null)
    {
        preg_match_all("/\##(.*?)\##/", $value, $matches);
        foreach (array_unique($matches[1]) as $attrId) {
            $attrValue = $product->getData($attrId);
            $value = str_replace('##'.$attrId.'##', $attrValue, $value);
        }
        $description = strip_tags($value);
        return $description;
    }

    /**
     * @param $product
     * @return int|null
     */
    public function getEtsyInventory($product)
    {
        $stockItem = $this->objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $stock = $stockItem->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $qty = (int)$stock->getQty();
        if ((int)$qty > 999) {
            $qty = 999;
        }
        $configInv = trim(
            $this->scopeConfig->getvalue(
                'etsy_config/product_setting/product_inventory'
            )
        );

        switch ($configInv) {
            case 'plusfixed':
                $fixedInv = trim(
                    $this->scopeConfig->getvalue(
                        'etsy_config/product_setting/fix_qty'
                    )
                );
                $qty = $this->forFixQty($qty, $fixedInv, $configInv);
                break;

            case 'minfixed':
                $fixedInv = trim(
                    $this->scopeConfig->getvalue(
                        'etsy_config/product_setting/fix_qty'
                    )
                );
                $qty = $this->forFixQty($qty, $fixedInv, $configInv);
                break;

            case 'differ_attr':
                $customQtyAttr = trim(
                    $this->scopeConfig->getvalue(
                        'etsy_config/product_setting/different_qty'
                    )
                );
                try {
                    $cQty = (int)$product->getData($customQtyAttr);
                } catch (\Exception $e) {
                    $this->getResponse()->setBody($e->getMessage());
                }
                $qty = ($cQty != 0) ? $cQty : $qty;
                break;

            default:
                return $qty;
        }
        return $qty;
    }

    /**
     * @param null $qty
     * @param null $fixedQty
     * @param $configPrice
     * @return int|null
     */
    public function forFixQty($qty = null, $fixedQty = null, $configQty)
    {
        if (is_numeric($fixedQty) && ($fixedQty != '')) {
            $fixedQty = (int)$fixedQty;
            if ($fixedQty > 0) {
                $qty = $configQty == 'plusfixed' ? (int)($qty + $fixedQty)
                    : (int)($qty - $fixedQty);
            }
        }
        return $qty;
    }


    /**
     * @param $product
     * @return float|null
     */
    public function getEtsyPrice($product)
    {
        $price = (float)$product->getFinalPrice();
        $configPrice = trim(
            $this->scopeConfig->getvalue(
                'etsy_config/product_setting/product_price'
            )
        );

        switch ($configPrice) {
            case 'plus_fixed':
                $fixedPrice = trim(
                    $this->scopeConfig->getvalue(
                        'etsy_config/product_setting/fix_price'
                    )
                );
                $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
                break;

            case 'plus_per':
                $percentPrice = trim(
                    $this->scopeConfig->getvalue(
                        'etsy_config/product_setting/percentage_price'
                    )
                );
                $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
                break;

            case 'min_fixed':
                $fixedPrice = trim(
                    $this->scopeConfig->getvalue(
                        'etsy_config/product_setting/fix_price'
                    )
                );
                $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
                break;

            case 'min_per':
                $percentPrice = trim(
                    $this->scopeConfig->getvalue(
                        'etsy_config/product_setting/percentage_price'
                    )
                );
                $price = $this->forPerPrice($price, $percentPrice, 'min_per');
                break;

            case 'differ':
                $customPriceAttr = trim(
                    $this->scopeConfig->getvalue(
                        'etsy_config/product_setting/different_price'
                    )
                );
                try {
                    $cprice = (float)$product->getData($customPriceAttr);
                } catch (\Exception $e) {
                    $this->getResponse()->setBody($e->getMessage());
                }
                $price = ($cprice != 0) ? $cprice : $price;
                break;

            default:
                return $price;

        }
        return $price;
    }

    /**
     * @param null        $price
     * @param null        $fixedPrice
     * @param $configPrice
     * @return float|null
     */
    public function forFixPrice($price = null, $fixedPrice = null, $configPrice)
    {
        if (is_numeric($fixedPrice) && ($fixedPrice != '')) {
            $fixedPrice = (float)$fixedPrice;
            if ($fixedPrice > 0) {
                $price = $configPrice == 'plus_fixed' ? (float)($price + $fixedPrice)
                    : (float)($price - $fixedPrice);
            }
        }
        return $price;
    }

    /**
     * @param null        $price
     * @param null        $percentPrice
     * @param $configPrice
     * @return float|null
     */
    public function forPerPrice($price = null, $percentPrice = null, $configPrice)
    {
        if (is_numeric($percentPrice)) {
            $percentPrice = (float)$percentPrice;
            if ($percentPrice > 0) {
                $price = $configPrice == 'plus_per' ?
                    (float)($price + (($price / 100) * $percentPrice))
                    : (float)($price - (($price / 100) * $percentPrice));
            }
        }
        return $price;
    }
}
