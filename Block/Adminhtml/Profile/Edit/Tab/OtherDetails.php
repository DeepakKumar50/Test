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
use Ced\Etsy\Model\Source\Profile\Recipient;
use Ced\Etsy\Model\Source\Profile\Occasion;

/**
 * Class OtherDetails
 * @package Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab
 */
class OtherDetails extends Generic
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
     * @param Recipient $recipient
     * @param Recipient $recipient
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Recipient $recipient,
        Occasion $occasion
    ) {
        $this->_coreRegistry = $registry;
        $this->recipient = $recipient;
        $this->occasion = $occasion;
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
            'other_details',
            [
                'legend'=>__('Other Details')
            ]
        );

        $fieldset->addField(
            'recipient',
            'select',
            [
                'name'      => "recipient",
                'label'     => __('Recipient'),
                'note'      => __('please select the recipient'),
                'value'     => $profile->getData('recipient'),
                'values'    =>  $this->recipient->getOptionArray(),
            ]
        );

        $fieldset->addField(
            'occasion',
            'select',
            [
                'name'      => "occasion",
                'label'     => __('Occasion'),
                'note'      => __('please select the occasion'),
                'value'     => $profile->getData('occasion'),
                'values'    =>  $this->occasion->getOptionArray(),
            ]
        );

        $fieldset->addField(
            'tags',
            'text',
            [
                'name'      => "tags",
                'label'     => __('Tags'),
                'note'      => __('specify the tags with "," sepration'),
                'value'     => $profile->getData('tags'),
            ]
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
