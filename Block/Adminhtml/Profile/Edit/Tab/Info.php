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
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Ced\Etsy\Model\Source\Profile\Status;

/**
 * Class Info
 * @package Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab
 */
class Info extends Generic
{
    /**
     * @var Status
     */
    public $status;

    /**
     * Info constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Status $status
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Status $status
    ) {
        $this->_coreRegistry = $registry;
        $this->status = $status;
        parent::__construct($context, $registry, $formFactory);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $form=$this->_formFactory->create();
        $profile = $this->_coreRegistry->registry('current_profile');
      
        $fieldset = $form->addFieldset(
            'profile_info',
            [
                'legend'=>__('Profile Information')
            ]
        );

        $fieldset->addField(
            'profile_code',
            'text',
            [
                'name'      => "profile_code",
                'label'     => __('Profile Code'),
                'note'      => __('For internal use. Must be unique with no spaces'),
                'class'     => 'validate-code',
                'required'  => true,
                'value'     => $profile->getData('profile_code'),
            ]
        );
    
        $fieldset->addField(
            'profile_name',
            'text',
            [
                'name'      => "profile_name",
                'label'     => __('Profile Name'),
                'class'     => '',
                'required'  => true,
                'value'    =>$profile->getData('profile_name'),
            ]
        );

        $fieldset->addField(
            'profile_status',
            'select',
            [
                'name'      => "profile_status",
                'label'     => __('Profile Status'),
                'value'     => $profile->getData('profile_status'),
                'values'    =>  $this->status->getOptionArray(),
            ]
        );

        $fieldset->addField(
            'in_profile_product',
            'hidden',
            [
                'name'  => 'in_profile_product',
                'id'    => 'in_profile_product',
            ]
        );

        $fieldset->addField(
            'in_profile_product_old',
            'hidden',
            [
                'name' => 'in_profile_product_old','id'=>"in_profile_product_old"
            ]
        );
    
        if ($profile->getId()) {
            $form->getElement('profile_code')->setDisabled(1);
        }
        
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
