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

namespace Ced\Etsy\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Connect
 * @package Ced\Etsy\Controller\Adminhtml\Config
 */
class Connect extends Action
{
    public $resultJsonFactory;
    /**
     * @var Filesystem
     */
    public $configResourceModel;

    /**
     * @var Data
     */
    public $helper;

    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configResourceModel = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $consumer_key = $this->getRequest()->getParam('consumerKey');
        $consumer_secret = $this->getRequest()->getParam('consumerSecretKey');
        $verfierCode = $this->getRequest()->getParam('oauth_verifier');
        $callbackUrl = $this->_objectManager->get('Magento\Framework\UrlInterface')->getCurrentUrl();
        if ($verfierCode != '') {
            $consumerkey = $this->scopeConfig->getValue('etsy_config/etsy_setting/consumer_key');
            $consumersecretkey = $this->scopeConfig->getValue('etsy_config/etsy_setting/consumer_secret_key');
            $accesstoken = $this->scopeConfig->getValue('etsy_config/etsy_setting/access_token');
            $accesstokensecret = $this->scopeConfig->getValue('etsy_config/etsy_setting/access_token_secret');
            $msg = '';
            try {
              $oAuth = new \OAuth($consumerkey, $consumersecretkey, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
              $oAuth->setToken($accesstoken, $accesstokensecret);
              $acc_token = $oAuth->getAccessToken(
                  "https://openapi.etsy.com/v2/oauth/access_token",
                  null,
                  $verfierCode, 'GET'
              );
            } catch (EtsyRequestException $e) {
                $msg = $e->getLastResponse();
            } catch (\Etsy\OAuthException $e) {    
                $msg = $e->getLastResponse();
            } catch (\Exception $e) {
                $msg = $e->getMessage();
            }
            if (isset($acc_token['oauth_token'])) {
                $this->configResourceModel->saveConfig('etsy_config/etsy_setting/access_token', $acc_token['oauth_token'], 'default', 0);
                $this->configResourceModel->saveConfig('etsy_config/etsy_setting/etsy_verifiercode', $verfierCode, 'default', 0);
                $this->configResourceModel->saveConfig('etsy_config/etsy_setting/access_token_secret', $acc_token['oauth_token_secret'], 'default', 0);
                $this->_objectManager->get('Magento\Framework\App\Cache\TypeListInterface')->cleanType('config');
                $this->messageManager->addSuccessMessage("Token Fetched Successfully");
            } else if($msg) {
                $this->messageManager->addErrorMessage($msg);
            } else {
                $this->messageManager->addErrorMessage("Token not Validated! please try again");
            }
            $responseUrl = str_replace('etsy/config/connect', 'admin/system_config/edit/section/etsy_config', $callbackUrl);
            $end = strpos($responseUrl, '/?isAjax');
            $redirectUrl = substr($responseUrl, 0, $end);
            return $this->_redirect($redirectUrl);
        } elseif ($consumer_key != '' && $consumer_secret != '') {
            try {
                $client = new \OAuth($consumer_key, $consumer_secret);
                $req_token = $client
                    ->getRequestToken("https://openapi.etsy.com/v2/oauth/request_token?scope=email_r%20listings_r%20listings_w%20listings_d%20transactions_r%20transactions_w%20billing_r%20profile_r%20profile_w%20address_r%20address_w%20favorites_rw%20shops_rw%20shops_rw%20cart_rw%20recommend_rw%20feedback_r%20treasury_r%20treasury_w", $callbackUrl, "GET");
                $url = $req_token['login_url'];
                $this->configResourceModel->saveConfig('etsy_config/etsy_setting/consumer_key', $req_token['oauth_consumer_key'], 'default', 0);

                $this->configResourceModel->saveConfig('etsy_config/etsy_setting/consumer_secret_key', $consumer_secret, 'default', 0);

                $this->configResourceModel->saveConfig('etsy_config/etsy_setting/access_token', $req_token['oauth_token'], 'default', 0);

                $this->configResourceModel->saveConfig('etsy_config/etsy_setting/access_token_secret', $req_token['oauth_token_secret'], 'default', 0);
                $this->_objectManager->get('Magento\Framework\App\Cache\TypeListInterface')->cleanType('config');
                $data ['msg'] = 'success';
                $data['log'] = $req_token;
                $data['data'] = $url;
            } catch (\Exception $e) {
                $data['msg'] = 'error';
                $data['data'] = $e->getMessage();
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($data);
    }
}
