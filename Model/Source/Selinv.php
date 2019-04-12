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
namespace Ced\Etsy\Model\Source;

class Selinv implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Default Magento Qunatity',
                'value' => 'final_inv'
            ],
            [
                'label' =>'Increase Fixed Quantity',
                'value' => 'plusfixed'
            ],
            [
                'label' => 'Decrease Fixed Quantity',
                'value' => 'minfixed'
            ],
            [
                'label' => 'Different Attribute',
                'value' => 'differ_attr'
            ],
        ];
    }
}
