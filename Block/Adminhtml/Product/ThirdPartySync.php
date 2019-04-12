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
 * @package   Ced_Walmart
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Block\Adminhtml\product;

/**
 * Class Import
 * @package Ced\Walmart\Block\Adminhtml\Product\Button
 */
class ThirdPartySync extends \Magento\Backend\Block\Widget\Container implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function getButtonData()
    {
        $button = [
            'id' => 'third_party_sync',
            'label' => __('Third Party Product Sync'),
            'class' => 'add',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->buttonOptions(),
        ];

        return $button;
    }

    /**
     * Retrieve options for 'Import Product' split button
     *
     * @return array
     */
    public function buttonOptions()
    {
        $splitButtonOptions = [];
        $splitButtonOptions['product_import_button'] = [
            'label' => __('Fetch Third Product Listing'),
            'onclick' => "setLocation('" . $this->getUrl('etsy/product/listing',['default' => true]) . "')",
            'default' => 'product_import_button',
        ];
        $splitButtonOptions['product_sync_button'] = [
            'label' => __('Sync Product'),
            'onclick' => "setLocation('" . $this->getUrl('etsy/product/thirdpartymasssync') . "')",
            'default' => false,
        ];

        return $splitButtonOptions;
    }
}