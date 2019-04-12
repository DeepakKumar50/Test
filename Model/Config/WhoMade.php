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
namespace Ced\Etsy\Model\Config;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Ced\Etsy\Helper\Data;

/**
 * Class WhoMade
 *
 * @package Ced\Etsy\Model\Config
 */
class WhoMade implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = 'i_did, collective, someone_else';
        $optionArray = explode(', ', $options);
        $returnOptions = [];
        foreach ($optionArray as $value) {
            $returnOptions[] = ['value' => $value, 'label' => __(str_replace('_', ' ', $value))];
        }
        return $returnOptions;
        /*return [
            ['value' => 'i_did', 'label' => __('I Did')],
            ['value' => 'collective', 'label' => __('Collective')],
            ['value' => 'someone_else', 'label' => __('Someone Else')]
        ];*/
    }

    /**
     * @return array
     */
    public function getLabel($options = [])
    {
        foreach ($this->toOptionArray() as $option) {
            $options[] =(string)$option['value'];
        }
        return $options;
    }
}
