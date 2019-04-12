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
namespace Ced\Etsy\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Etsy::profile';
    public $_coreRegistry;
    public $_cache;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\Etsy\Helper\Cache $cache
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_cache = $cache;
    }

    /**
     *
     * @param string $idFieldName
     * @return mixed
     */
    protected function _initProfile($idFieldName = 'pcode')
    {

        $profileCode = $this->getRequest()->getParam($idFieldName);
        $profile = $this->_objectManager->get('Ced\Etsy\Model\Profile');

        if ($profileCode) {
            $profile->loadByField('profile_code', $profileCode);
        }

        $this->getRequest()->setParam('is_etsy', 1);
        $this->_coreRegistry->register('current_profile', $profile);
        return $this->_coreRegistry->registry('current_profile');
    }

    /**
     *
     */
    public function execute()
    {
        $optAttribute = $etsyAttribute = $etsyReqOptAttribute = [];
        $data = $this->_objectManager->create('Magento\Config\Model\Config\Structure\Element\Group')->getData();
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_context = $this->_objectManager->get('Magento\Framework\App\Helper\Context');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $tab = $this->getRequest()->getParam('tab', false);
        $pcode = $this->getRequest()->getParam('pcode', false);
        $profileData = $this->getRequest()->getPostValue();
        $category[] = isset($profileData['level_0']) ? $profileData['level_0'] : "";
        $category[] = isset($profileData['level_1']) ? $profileData['level_1'] : "";
        $category[] = isset($profileData['level_2']) ? $profileData['level_2'] : "";
        $category[] = isset($profileData['level_3']) ? $profileData['level_3'] : "";
        $category[] = isset($profileData['level_4']) ? $profileData['level_4'] : "";
        $category[] = isset($profileData['level_5']) ? $profileData['level_5'] : "";
        $category[] = isset($profileData['level_6']) ? $profileData['level_6'] : "";

        $profileData = json_decode(json_encode($profileData), 1);

        $inProfile = $this->getRequest()->getParam('in_profile');
        $profileProducts = $this->getRequest()->getParam('in_profile_products', null);
        if(strlen($profileProducts) > 0 ) {
            $profileProducts  = explode(',' , $this->getRequest()->getParam('in_profile_products', null));
        } else {
            $profileProducts = [];
        }

        $profileData = json_decode(json_encode($profileData), 1);

        $resource = $this->getRequest()->getPost('resource', false);

        try {
            $profile = $this->_initProfile('pcode');
            if (!$profile->getId() && $pcode) {
                $this->messageManager->addErrorMessage(__('This Profile no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            $pname = $profileData['profile_name'];
            if (isset($profileData['profile_code'])) {
                $pcode = $profileData['profile_code'];
                $profileCollection = $this->_objectManager->get('Ced\Etsy\Model\Profile')->getCollection()
                    ->addFieldToFilter('profile_code', $profileData['profile_code']);
                if (count($profileCollection) > 0) {
                    $this->messageManager->addErrorMessage(__('This Profile Already Exist Please Change Profile Code'));
                    $this->_redirect('*/*/new');
                    return;
                }
            }
 
            $profile->addData($profileData);
            $profile->setProfileCategory(json_encode($category));
            // save attribute
            if (isset($profileData['variant_attributes'])) {
                $profile->setConfigAttributes(json_encode($profileData['variant_attributes']));
            } else {
                $profile->setConfigAttributes('');
            }
            // save required attribute
            $reqAttribute1 = [];
            $optAttribute1 = [];
            if (!empty($profileData['required_attributes'])) {
                $temAttribute1 = $this->unique_multidim_array($profileData['required_attributes'], 'etsy_attribute_name');
                $temp3 = $temp4 = [];
                foreach ($temAttribute1 as $item) {
                    if ($item['required']) {
                        $temp3['etsy_attribute_name'] = $item['etsy_attribute_name'];
                        $temp3['etsy_attribute_type'] = $item['etsy_attribute_type'];
                        $temp3['magento_attribute_code'] = $item['magento_attribute_code'];
                        if (isset($item['default'])) {
                            $temp3['default'] = $item['default'];
                        }
                        $temp3['required'] = $item['required'];
                        $reqAttribute1[] = $temp3;
                    } else {
                        $temp4['etsy_attribute_name'] = $item['etsy_attribute_name'];
                        $temp4['etsy_attribute_type'] = $item['etsy_attribute_type'];
                        $temp4['magento_attribute_code'] = $item['magento_attribute_code'];
                        if (isset($item['default'])) {
                            $temp4['default'] = $item['default'];
                        }
                        $temp4['required'] = 0;
                        $optAttribute1[] = $temp4;
                    }
                }
                $etsyReqOptAttribute['required_attributes'] = $reqAttribute1;
                $etsyReqOptAttribute['optional_attributes'] = $optAttribute1;

                $profile->setProfileReqOptAttribute(json_encode($etsyReqOptAttribute));
            } else {
                $profile->setProfileReqOptAttribute('');
            }

            // save category recipient
            if (isset($profileData['recipient'])) {
                $profile->setRecipient($profileData['recipient']);
            }
            // save category occasion
            if (isset($profileData['occasion'])) {
                $profile->setOccasion($profileData['occasion']);
            }
            // save category tags
            if (isset($profileData['tags'])) {
                $profile->setTags($profileData['tags']);
            }

            //save profile
            $profile->save();

            //cache values
            $oldProfileProducts = $this->_objectManager->create("Ced\Etsy\Model\Profileproducts")
                ->getProfileProducts($profile->getId());


            $deleteProds = array_diff($oldProfileProducts, $profileProducts);
            $addProds = array_diff($profileProducts, $oldProfileProducts);


            foreach ($deleteProds as $oUid) {
                $this->_deleteProductFromProfile($oUid);
                $this->_cache->removeValue(\Ced\Etsy\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$oUid);
            }

            foreach ($addProds as $nRuid) {
                if($this->_addProductToProfile($nRuid, $profile->getId()))
                    $this->_cache->setValue(\Ced\Etsy\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$nRuid, $profile->getId());
            }

            if ($redirectBack && $redirectBack == 'edit') {
                $this->messageManager->addSuccessMessage(
                    __(
                        '
		   		You Saved The Etsy Profile And Its Products.
		   			'
                    )
                );
                $this->_redirect(
                    '*/*/edit',
                    [
                        'pcode' => $pcode,
                    ]
                );
            } elseif ($redirectBack && $redirectBack == 'upload') {
                $this->messageManager->addSuccessMessage(
                    __(
                        '
		   		You Saved The Etsy Profile And Its Products. Upload Product Now.
		   			'
                    )
                );
                $this->_redirect(
                    'etsy/products/index',
                    [
                        'profile_id' => $profile->getId()
                    ]
                );
            } else {
                $this->messageManager->addSuccessMessage(
                    __(
                        '
		   		You Saved The Etsy Profile And Its Products.
		   		'
                    )
                );
                $this->_redirect('*/*/');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __(
                    '
		   		Unable to Save Profile Please Try Again.
		   			' . $e->getMessage()
                )
            );
            $this->_redirect(
                '*/*/edit',
                ['pcode' => $pcode]
            );
        }

        return;
    }

    /**
     * @param $productId
     * @param $profileId
     * @return bool
     */
    public function _addProductToProfile($productId, $profileId)
    {
        $profileproduct = $this->_objectManager->create("Ced\Etsy\Model\Profileproducts")
            ->deleteFromProfile($productId);

        if ($profileproduct->profileProductExists($productId, $profileId) === true) {
            return false;
        } else {
            $profileproduct->setProductId($productId);
            $profileproduct->setProfileId($profileId);
            $profileproduct->save();
            return true;
        }
    }

    /**
     * @param $productId
     * @return bool
     * @throws \Exception
     */
    public function _deleteProductFromProfile($productId)
    {
        try {
            $this->_objectManager->create("Ced\Etsy\Model\Profileproducts")
                ->deleteFromProfile($productId);
        } catch (\Exception $e) {
            throw $e;
            return false;
        }
        return true;
    }

    /**
     * @param $array
     * @param $key
     * @return array
     */
    public function unique_multidim_array($array, $key)
    {
        $temp_array = [];
        $i = 0;
        $key_array = [];

        foreach ($array as $val) {

            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}
