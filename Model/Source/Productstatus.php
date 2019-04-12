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

/**
 * Class Productstatus
 * @package Ced\Etsy\Model\Source
 */
class Productstatus extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const NOT_UPLOADED = 'notuploaded';
    const INVALID = 'invalid';
    const INACTIVE = 'inactive';
    const UPLOADED = 'uploaded';
    const ENDED = 'ended';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
    * @return array
    */
    public function getAllOptions()
    {
        return [
            [
                'value' => self::NOT_UPLOADED,
                'label' => __('Not Uploaded')
            ],            
            [
                'value' => self::INVALID,
                'label' => __('Invalid')
            ],
            [
                'value' => self::UPLOADED,
                'label' => __('Uploaded on Etsy')
            ],                      
            [
                'value' => self::INACTIVE,
                'label' => __('Inactive On Etsy')
            ],
            [
                'value' => self::ENDED,
                'label' => __('Deleted On Etsy')
            ],
        ];
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] =(string)$option['label'];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * @param int|string $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

}
