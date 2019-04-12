<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Etsy
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab\Attribute;

class Configattribute extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    protected $_template = 'Ced_Etsy::profile/attribute/config_attribute.phtml';


    protected  $_objectManager;

    protected  $_coreRegistry;

    protected  $_profile;

    protected  $_etsyAttribute;

    public  $json;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $json,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->json = $json;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Attribute'), 'onclick' => 'return configAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');
        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Retrieve etsy attributes
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getEtsyConfigAttributes($subcatattribute =null )
    {
        $configAttribute = [];
        if(isset($this->_profile )){
            $configAttributes=json_decode($this->_profile->getConfigAttributes(),1);
           if( isset($configAttributes)){
            foreach ($configAttributes as $key => $value) {
                $this->_etsyAttribute[$value['etsy_attribute_name']] = $value['etsy_attribute_name'];
                $temp = [];
                $temp['etsy_attribute_name'] = $value['etsy_attribute_name'];
                $temp['magento_attribute_code'] = $value['magento_attribute_code'];
                $temp['etsy_attribute_type'] = $value['etsy_attribute_type'];
                $temp['property_id'] = $value['etsy_property_id'];
                $temp['etsy_enum'] =  isset($value['etsy_enum_val'])?$value['etsy_enum_val']:'{}';
                $temp['default']=isset($value['default'])?$value['default']:'';
                $temp['option_values'] = $value['option_mapping'];
                $temp['required'] = 0;
                $configAttribute[$value['etsy_attribute_name']] = $temp;

            }
            }
        }else {
            if (isset($subcatattribute['results']) && isset($subcatattribute['results'][0])) {
                foreach ($subcatattribute['results'] as $key => $value) {
                    if ($value['name'] == 'Holiday') {
                        continue;
                    }
                    if ((isset($value['possible_values']) && !empty($value['possible_values']))|| $value['property_id']==513 || $value['property_id']==514 ) {
                        $enum = [];
                        $enumjson = '';
                        if(isset($value['possible_values']) && !empty($value['possible_values'])) {
                            foreach ($value['possible_values'] as $key2 => $enumvalue) {
                                $enum[] = (str_replace("'","", $enumvalue['value_id']) . ':' . str_replace("'","", $enumvalue['name']));
                            }
                            $type='select';
                            $enumjson = json_encode($enum);
                        }else{
                            $type='text';
                            $enumjson = '';
                        }

                        $this->_etsyAttribute[str_replace("'","", $value['name'])] = str_replace("'","", $value['name']);
                        $temp = [];
                        $temp['etsy_attribute_name'] =str_replace("'","", $value['name']) ;
                        $temp['magento_attribute_code'] = '';
                        $temp['etsy_attribute_type'] = $type;
                        $temp['property_id'] = $value['property_id'];
                        $temp['etsy_enum'] = $enumjson;
                        $temp['default']=isset($value['default'])?$value['default']:'';
                        $temp['option_values'] = '';
                        $temp['required'] = 0;
                        $configAttribute[str_replace("'","", $value['name'])] = $temp;
                    }
                }
            }
        }

        $this->_etsyAttribute = $configAttribute;
        return $this->_etsyAttribute;
    }


    /**
     * Retrieve magento attributes
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->addFieldToFilter('frontend_input', ['in' => ['select', 'multiselect']]);

        $magentoattributeCodeArray = [];
        foreach ($attributes as $attribute){

            $type = "";
            $optionValues = "";
            $attributeOptions = $attribute->getSource()->getAllOptions(false);
            if (!empty($attributeOptions) and is_array($attributeOptions)) {
                $type = " [ select ]";
                foreach ($attributeOptions as &$option) {
                    if (isset($option['label']) and is_object($option['label'])) {
                        $option['label'] = $option['label']->getText();
                    }
                }
                $attributeOptions = str_replace('\'', '&#39;', $this->json->jsonEncode($attributeOptions));
                $optionValues = addslashes($attributeOptions);
            }
            $mattributecode = '--please select--';

            $magentoattributeCodeArray[''] =
                [
                    'attribute_code' => $mattributecode,
                    'attribute_type' => '',
                    'input_type' => '',
                    'option_values' => ''
                ];
            $magentoattributeCodeArray['default'] =
                [
                    'attribute_code' =>"-- Set Default Value --",
                    'attribute_type' => '',
                    'input_type' => 'select',
                    'option_values' => ''
                ];
            if($attribute->getFrontendInput() =='select' && $optionValues){
                $magentoattributeCodeArray[$attribute->getAttributecode()] =
                    [
                        'attribute_code' => $attribute->getAttributecode(),
                        'attribute_type' => $attribute->getFrontendInput(),
                        'input_type' => 'select',
                        'option_values' => $optionValues,
                    ];
            }
            else{
                $magentoattributeCodeArray[$attribute->getAttributecode()] =
                    [
                        'attribute_code' => $attribute->getAttributecode(),
                        'attribute_type' => $attribute->getFrontendInput(),
                        'input_type' => 'select',
                        'option_values' => $optionValues,
                    ];
            }
        }
        return $magentoattributeCodeArray;
    }


     public function getEtsyAttributeValuesMapping($subcatattribute=null)
     {
        $data = [];
        if ($this->_profile && $this->_profile->getId()>0) {
            $configdata = json_decode($this->_profile->getConfigAttributes(), true);
            if (is_array($configdata)) {
                foreach ($configdata as $key => $value) {
                $this->_etsyAttribute[$value['etsy_attribute_name']] = $value['etsy_attribute_name'];
                $temp = [];
                $temp['etsy_attribute_name'] = $value['etsy_attribute_name'];
                $temp['magento_attribute_code'] = $value['magento_attribute_code'];
                $temp['etsy_attribute_type'] = $value['etsy_attribute_type'];
                $temp['property_id'] = $value['etsy_property_id'];
                $temp['etsy_enum'] = isset($value['etsy_enum_val'])?$value['etsy_enum_val']:'{}';
                $temp['default']=isset($value['default'])?$value['default']:'';
                $temp['option_values'] = $value['option_mapping'];
                $temp['required'] = 0;
                $configAttribute[$value['etsy_attribute_name']] = $temp;
            }
            $data=['profile_id'=>$this->_profile->getId(),'data'=>$configAttribute];
            }
        } else {
            if(!$this->_etsyAttribute) {
                if (isset($subcatattribute['results'])) {
                    $this->_etsyAttribute = $this->getEtsyConfigAttributes($subcatattribute);
                }
            }
            foreach ($this->_etsyAttribute as $key => $value) {
                if (isset($value['magento_attribute_code']) ) {
                    $data[] = $value;
                }
            }
            $data=['data'=>$data];
        }
        return $data;
    }


    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
