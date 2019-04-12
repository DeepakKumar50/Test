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

namespace Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab\Attribute;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Ced\Etsy\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class CategoryJs
 * @package Ced\Etsy\Block\Adminhtml\Profile\Edit\Tab\Attribute
 */
class CategoryJs extends Widget implements RendererInterface
{
	/**
     * @var string
     */
    public $_template = 'profile/categoryMapping.phtml';

    /**
     * @var
     */
    public  $_profile;
    /**
     * @var Data
     */
    public $helper;
    /**
     * CategoryJs constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        Registry $registry,
        $data = []
    ) {
        $this->helper = $helper;
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        parent::__construct($context, $data);
    }

    /**
     * @param $level
     * @return array
     */
    public function getLevel($level)
    {
        $option = [];
        $folderPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('ced/etsy/');

        $path = $folderPath. '/categoryLevel-'.$level.'.json';
        $category = $this->helper->loadFile($path);
        $options = !empty($category) ? $category : [];
        foreach ($options as $value) {
            $option[] = $value;
        }
        return $option;
    }

    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
