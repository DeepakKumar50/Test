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

class Occasion implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = 'anniversary, baptism, bar_or_bat_mitzvah, birthday, canada_day, chinese_new_year, cinco_de_mayo, confirmation, christmas, day_of_the_dead, easter, eid, engagement, fathers_day, get_well, graduation, halloween, hanukkah, housewarming, kwanzaa, prom, july_4th, mothers_day, new_baby, new_years, quinceanera, retirement, st_patricks_day, sweet_16, sympathy, thanksgiving, valentines, wedding';
        $optionArray = explode(', ', $options);
        $returnOptions = [];
        $returnOptions[] = ['value' => '', 'label' => __("Please Select the Occasion")];
        foreach ($optionArray as $value) {
            $returnOptions[] = ['value' => $value, 'label' => __(str_replace('_', ' ', $value))];
        }
        return $returnOptions;
        /*return [
            ['value' => "", 'label' => __('Please Select the Occasion')],
            ['value' => 'anniversary', 'label' => __('Anniversary')],
            ['value' => 'baptism', 'label' => __('Baptism')],
            ['value' => 'bar_or_bat_mitzvah', 'label' => __('Bar Or Bat Mitzvah')],
            ['value' => 'birthday', 'label' => __('Birthday')],
            ['value' => 'canada_day', 'label' => __('Canada Day')],
            ['value' => 'chinese_new_year', 'label' => __('chinese_new_year')],
            ['value' => 'cinco_de_mayo', 'label' => __('cinco_de_mayo')],
            ['value' => 'confirmation', 'label' => __('confirmation')],
            ['value' => 'christmas', 'label' => __('christmas')],
            ['value' => 'day_of_the_dead', 'label' => __('day_of_the_dead')],
            ['value' => 'easter', 'label' => __('easter')],
            ['value' => 'eid', 'label' => __('eid')],
            ['value' => 'engagement', 'label' => __('engagement')],
            ['value' => 'fathers_day', 'label' => __('fathers_day')],
            ['value' => 'get_well', 'label' => __('get_well')],
            ['value' => 'graduation', 'label' => __('graduation')],
            ['value' => 'halloween', 'label' => __('halloween')],
            ['value' => 'hanukkah', 'label' => __('hanukkah')],
            ['value' => 'housewarming', 'label' => __('housewarming')],
            ['value' => 'kwanzaa', 'label' => __('kwanzaa')],
            ['value' => 'prom', 'label' => __('prom')],
            ['value' => 'july_4th', 'label' => __('july_4th')],
            ['value' => 'mothers_day', 'label' => __('mothers_day')],
            ['value' => 'new_baby', 'label' => __('new_baby')],
            ['value' => 'new_years', 'label' => __('new_years')],
            ['value' => 'quinceanera', 'label' => __('quinceanera')],
            ['value' => 'retirement', 'label' => __('retirement')],
            ['value' => 'st_patricks_day', 'label' => __('st_patricks_day')],
            ['value' => 'sweet_16', 'label' => __('sweet_16')],
            ['value' => 'sympathy', 'label' => __('sympathy')],
            ['value' => 'thanksgiving', 'label' => __('thanksgiving')],
            ['value' => 'valentines', 'label' => __('valentines')],
            ['value' => 'wedding', 'label' => __('wedding')]
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
