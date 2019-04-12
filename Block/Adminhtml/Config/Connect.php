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

namespace Ced\Etsy\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template;

class Connect extends Field
{
    /**
     * @return $this
     */
    public $_consumerKey = 'etsy_config_etsy_setting_consumer_key';
    public $_consumerSecretKey = 'etsy_config_etsy_setting_consumer_secret_key';


    public function _prepareLayout()
    {


        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('config/connect.phtml');
        }
        return $this;
    }

    public function setCusumer($consumerKey)
    {
        $this->_consumerKey = $consumerKey;
        return $this;
    }

    public function getCusumer()
    {
        return $this->_consumerKey;
    }

    public function setSecret($consumerSecretKey)
    {
        $this->_consumerSecretKey = $consumerSecretKey;
        return $this;
    }

    public function getSecret()
    {
        return $this->_consumerSecretKey;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = $originalData['button_label'];
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('etsy/config/connect'),
            ]
        );
        return $this->_toHtml();
    }
}
