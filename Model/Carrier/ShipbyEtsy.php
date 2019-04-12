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

namespace Ced\Etsy\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

/**
 * Class ShipbyEtsy
 *
 * @package Ced\Etsy\Model\Carrier
 */
class ShipbyEtsy extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    public $_code = 'shipbyetsy';
    /**
     * @var LoggerInterface
     */
    public $_logger;
    /**
     * @var bool
     */
    public $_isFixed = true;
    /**
     * @var ResultFactory
     */
    public $_rateResultFactory;
    /**
     * @var MethodFactory
     */
    public $_rateMethodFactory;

    /**
     * ShipbyEtsy constructor.
     *
     * @param State                $appState
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory         $rateErrorFactory
     * @param LoggerInterface      $logger
     * @param ResultFactory        $rateResultFactory
     * @param MethodFactory        $rateMethodFactory
     * @param array                $data
     */
    public function __construct(
        State $appState,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
    
        $this->appState = $appState;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if ($this->appState->getAreaCode() != 'adminhtml' && $this->appState->getAreaCode() != 'crontab') {
            return false;
        }

        $price = $this->getConfigData('price');
        if (!$price) {
            $price = 0;
        }

        $this->getConfigFlag('handling');

        /**
 * @var \Magento\Shipping\Model\Rate\Result $result 
*/
        $result = $this->_rateResultFactory->create();

        /**
 * @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method 
*/
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setMethod($this->_code);

        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($price);
        $method->setCost(0);

        $result->append($method);
        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('title')];
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }
}
