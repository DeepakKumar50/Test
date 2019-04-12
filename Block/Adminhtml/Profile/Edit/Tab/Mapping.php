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
namespace Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab;

use \Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;
use Ced\Etsy\Model\Source\Profile\Category\Rootlevel;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

/**
 * Class Mapping
 * @package Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab
 */
class Mapping extends Generic
{
    /**
     * @var Rootlevel
     */
    public $rootlevel;

    /**
     * Mapping constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Rootlevel $rootlevel
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Rootlevel $rootlevel
    )
    {
        $this->_coreRegistry = $registry;
        $this->rootlevel = $rootlevel;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $this->_coreRegistry->registry('current_profile');

        $fieldset = $form->addFieldset('category', ['legend' => __('Category Mapping')]);

        $fieldset->addField(
            'level_0',
            'select',
            [
                'name' => 'level_0',
                'label' => __('Root Level Category'),
                'title' => __('Root Level Category'),
                'required' => true,
                'values' => $this->rootlevel->toOptionArray()
            ]
        );

        $fieldset->addField(
            'level_1',
            'select',
            [
                'name' => 'level_1',
                'label' => __('Level 1 Category'),
                'title' => __('Level 1 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_2',
            'select',
            [
                'name' => 'level_2',
                'label' => __('Level 2 Category'),
                'title' => __('Level 2 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_3',
            'select',
            [
                'name' => 'level_3',
                'label' => __('Level 3 Category'),
                'title' => __('Level 3 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_4',
            'select',
            [
                'name' => 'level_4',
                'label' => __('Level 4 Category'),
                'title' => __('Level 4 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_5',
            'select',
            [
                'name' => 'level_5',
                'label' => __('Level 5 Category'),
                'title' => __('Level 5 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'level_6',
            'select',
            [
                'name' => 'level_6',
                'label' => __('Level 6 Category'),
                'title' => __('Level 6 Category'),
                'required' => true,
                'values' => ""
            ]
        );

        $fieldset->addField(
            'category_js',
            'text',
            [
                'label' => __('Category JS Mapping'),
                'class' => 'action',
                'name' => 'category_js_mapping'
            ]
        );

        $locations = $form->getElement('category_js');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab\Attribute\CategoryJs')
        );

        $fieldset = $form->addFieldset(
            'required_attributes',
            [
                'legend' => __('Etsy-Magento Required Attributes Mapping')
            ]
        );

        $fieldset->addField(
            'required_attribute',
            'text',
            [
                'label' => __('Attribute Mapping'),
                'class' => 'action',
                'name' => 'required_attribute'
            ]
        );

        $locations = $form->getElement('required_attribute');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab\Attribute\Requiredattribute')
        );
        $fieldset = $form->addFieldset('config_attributes', array('legend'=>__('Etsy / Magento Attribute Mapping (Variant Attribute Mapping)')));

        $fieldset->addField('config_attribute', 'text', [
                'label'     => __('Config Attribute Mapping'),
                'class'     => 'action',
                'name'      => 'required_attribute'
            ]
        );

        $locations = $form->getElement('config_attribute');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab\Attribute\Configattribute')
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }
}