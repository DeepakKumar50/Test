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

use Magento\Framework\Data\OptionSourceInterface;

class Style implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [
            266817173 => 'Abstract',
            266817175 => 'African',
            266817177 => 'Art Deco',
            266817179 => 'Art Nouveau',
            266817183 => 'Asian',
            266817187 => 'Athletic',
            266817191 => 'Avant Garde',
            304956147 => 'Beach',
            266817193 => 'Boho',
            266817195 => 'Burlesque',
            305058069 => 'Cottage Chic',
            266817199 => 'Country Western',
            266817201 => 'Edwardian',
            266817203 => 'Fantasy',
            266817205 => 'Folk',
            266817207 => 'Goth',
            266817209 => 'High Fashion',
            266817213 => 'Hip Hop',
            266817215 => 'Hippie',
            266817217 => 'Hipster',
            266817219 => 'Historical',
            266817221 => 'Hollywood Regency',
            266817223 => 'Industrial',
            266817225 => 'Kawaii',
            266817227 => 'Kitsch',
            266817229 => 'Mediterranean',
            304248594 => 'Mid Century',
            265747030 => 'Military',
            265747032 => 'Minimalist',
            265747034 => 'Mod',
            265747036 => 'Modern',
            265747038 => 'Nautical',
            265747040 => 'Neoclassical',
            265747042 => 'Preppy',
            265747044 => 'Primitive',
            265747046 => 'Regency',
            265747050 => 'Renaissance',
            265747052 => 'Resort',
            265747056 => 'Retro',
            265747058 => 'Rocker',
            265747062 => 'Rustic',
            265747066 => 'Sci Fi',
            302001500 => 'Southwestern',
            302781055 => 'Spooky',
            302785605 => 'Steampunk',
            302785867 => 'Techie',
            302078700 => 'Traditional',
            302786453 => 'Tribal',
            302786877 => 'Victorian',
            302080242 => 'Waldorf',
            302081124 => 'Woodland',
            302789403 => 'Zen'
            ];
        $returnOptions = [];
        foreach ($optionArray as $key => $value) {
            $returnOptions[] = ['value' => $key, 'label' => $value];
        }
        return $returnOptions;
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
