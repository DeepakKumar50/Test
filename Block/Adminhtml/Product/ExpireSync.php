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

use Magento\Backend\Block\Widget;

/**
 * Class ExpireSync
 * @package Ced\Etsy\Block\Adminhtml\Product
 */
class ExpireSync extends Widget\Container
{
    /**
     * ExpireSync constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }
}