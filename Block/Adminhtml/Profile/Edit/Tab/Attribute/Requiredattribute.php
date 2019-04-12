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

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Framework\Data\Form\Element\AbstractElement;
/**
 * Class Requiredattribute
 * @package Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class Requiredattribute extends Widget implements RendererInterface
{

    /**
     * @var string
     */
    public $_template = 'Ced_Etsy::profile/attribute/required_attribute.phtml';

    public $_objectManager;

    public $_coreRegistry;

    public $_profile;

    public $_etsyAttribute;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
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
            ['label' => __('Add Attribute'), 'onclick' => 'return requiredAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return array
     */
    public function getEtsyAttributes()
    {
        $shippingId = $this->_objectManager->get('Ced\Etsy\Model\Config\ShippingTemplate')->getLabel();
        $shopSectionId = $this->_objectManager->get('Ced\Etsy\Model\Config\ShopSection')->getLabel();
        $requiredAttribute = [
            'Product Name' => ['etsy_attribute_name' => 'name', 'etsy_attribute_type' => 'text', 'etsy_attribute_enum' => '', 'magento_attribute_code' => 'name', 'required' => 1],
            'SKU' => ['etsy_attribute_name' => 'sku', 'etsy_attribute_type' => 'text', 'etsy_attribute_enum' => '', 'magento_attribute_code' => 'sku', 'required' => 1],            
            'Description' => ['etsy_attribute_name' => 'description', 'etsy_attribute_type' => 'textarea', 'etsy_attribute_enum' => '', 'magento_attribute_code' => 'description', 'required' => 1] 
        ];
        $optionalAttribues = [
            'When Made' => ['etsy_attribute_name' => 'when_made', 'etsy_attribute_type' => 'text', 'etsy_attribute_enum' => 'made_to_order, 2010_2018, 2000_2009, 1999_1999, before_1999, 1990_1998, 1980s, 1970s, 1960s, 1950s, 1940s, 1930s, 1920s, 1910s, 1900s, 1800s, 1700s, before_1700', 'magento_attribute_code' => ''],
            'Who Made' => ['etsy_attribute_name' => 'who_made', 'etsy_attribute_type' => 'text', 'etsy_attribute_enum' => 'i_did, collective, someone_else', 'magento_attribute_code' => ''],
            'Shipping Template ID' => ['etsy_attribute_name' => 'shipping_template_id', 'etsy_attribute_type' => 'text', 'etsy_attribute_enum' => implode(',', $shippingId), 'magento_attribute_code' => ''],
            'Shop Section ID' => ['etsy_attribute_name' => 'shop_section_id', 'etsy_attribute_type' => 'text', 'etsy_attribute_enum' => implode(',', $shopSectionId), 'magento_attribute_code' => ''],
            'State' => ['etsy_attribute_name' => 'state', 'etsy_attribute_type' => 'text', 'etsy_attribute_enum' => 'active, draft', 'magento_attribute_code' => ''],
            'Meterial' => ['etsy_attribute_name' => 'materials', 'etsy_attribute_type' => 'text', 'etsy_attribute_enum' => '', 'magento_attribute_code' => ''],
            'Is Customizable' => ['etsy_attribute_name' => 'is_customizable','etsy_attribute_type' => 'text', 'etsy_attribute_enum' => 'false,true'],
            'Non Taxable' => ['etsy_attribute_name' => 'non_taxable','etsy_attribute_type' => 'text', 'etsy_attribute_enum' => 'false,true'],
            'Is Supply' => ['etsy_attribute_name' => 'is_supply','etsy_attribute_type' => 'text', 'etsy_attribute_enum' => 'false,true']
            ];

        $this->_etsyAttribute[] = [
            'label' => __('Required Attributes'),
            'value' => $requiredAttribute
        ];
        $this->_etsyAttribute[] = array(
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        );

        return $this->_etsyAttribute;
    }

    /**
     * @return mixed
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->getItems();

        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = "--Set Default Value--";
        foreach ($attributes as $attribute){
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }
        return $magentoattributeCodeArray;
    }

    /**
     * @return array|mixed
     */
    public function getMappedAttribute()
    {
        $data = $this->_etsyAttribute[0]['value'];
        if ($this->_profile && $this->_profile->getId() > 0) {
            $data = json_decode($this->_profile->getProfileReqOptAttribute(), true);
            if(isset($data['required_attributes']) && isset($data['optional_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
        }
        return $data;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
