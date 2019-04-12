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
namespace Ced\Etsy\Model\Source\Profile;

use Magento\Framework\Data\OptionSourceInterface;

class Recipient implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = 'men, women, unisex_adults, teen_boys, teen_girls, teens, boys, girls, children, baby_boys, baby_girls, babies, birds, cats, dogs, pets, not_specified';
        $optionArray = explode(', ', $options);
        $returnOptions = [];
        $returnOptions[] = ['value' => '', 'label' => __("Please Select the Recipient")];
        foreach ($optionArray as $value) {
            $returnOptions[] = ['value' => $value, 'label' => __(str_replace('_', ' ', $value))];
        }
        return $returnOptions;
        /*return [
            ['value' => '', 'label' => __('Please Select The Recipient')],
            ['value' => 'men', 'label' => __('men')],
            ['value' => 'women', 'label' => __('women')],
            ['value' => 'unisex_adults', 'label' => __('unisex_adults')],
            ['value' => 'teen_boys', 'label' => __('teen_boys')],
            ['value' => 'teen_girls', 'label' => __('teen_girls')],
            ['value' => 'teens', 'label' => __('teens')],
            ['value' => 'boys', 'label' => __('boys')],
            ['value' => 'girls', 'label' => __('girls')],
            ['value' => 'children', 'label' => __('children')],
            ['value' => 'baby_boys', 'label' => __('baby_boys')],
            ['value' => 'baby_girls', 'label' => __('baby_girls')],
            ['value' => 'babies', 'label' => __('babies')],
            ['value' => 'birds', 'label' => __('birds')],
            ['value' => 'cats', 'label' => __('cats')],
            ['value' => 'dogs', 'label' => __('dogs')],
            ['value' => 'pets', 'label' => __('pets')],
            ['value' => 'not_specified', 'label' => __('not_specified')]
        ];*/
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }
}
