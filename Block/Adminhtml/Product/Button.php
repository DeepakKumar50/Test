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

namespace Ced\Etsy\Block\Adminhtml\Product;

/**
 * Class Button
 * @package Ced\Etsy\Block\Adminhtml\Product
 */
class Button extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Button constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'etsy_product_button',
            'label' => __('Bulk Status Sync'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];

        $splitButtonOptions['active_sync'] = [
            'label' => __('Active Status'),
            'onclick' => "setLocation('" . $this->getUrl(
                    'etsy/product/bulkactivesync') . "')",
            'default' => true,
        ];

        $splitButtonOptions['inactive_sync'] = [
            'label' => __('Inactive Status'),
            'onclick' => "setLocation('" . $this->getUrl(
                    'etsy/product/bulkinactivesync') . "')",
            'default' => false,
        ];

        $splitButtonOptions['expire_sync'] = [
            'label' => __('Expire Status'),
            'onclick' => "setLocation('" . $this->getUrl(
                    'etsy/product/bulkexpiresync') . "')",
            'default' => false,
        ];

        $splitButtonOptions['draft_sync'] = [
            'label' => __('Draft Status'),
            'onclick' => "setLocation('" . $this->getUrl(
                    'etsy/product/bulkdraftsync') . "')",
            'default' => false,
        ];

        return $splitButtonOptions;
    }

}
