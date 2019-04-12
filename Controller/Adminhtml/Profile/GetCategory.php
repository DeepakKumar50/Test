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

use Magento\Framework\View\Result\PageFactory;
use Ced\Etsy\Helper\Data;
use Magento\Backend\App\Action;
 
class GetCategory extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Etsy::profile';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_coreRegistry;

    /**
     * @var
     */
    public $helepr;

    /**
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $catName = $this->getRequest()->getParam('catName');
        $parentCatName = $this->getRequest()->getParam('parentCategory');
        $html = "";
        if ($catName) {
            $catArray = explode('.', $catName);
            $catName = $catArray[1];
            if ($parentCatName) {
                $parentCatArray = explode('.', $parentCatName);
                $parentCatName = $parentCatArray[1];
                $html .= '<option value="">Please select level 2 category</option>';
                $response = $this->helper->ApiObject()->findAllSubCategoryChildren(['params' => ['tag'=>$parentCatName, 'subtag'=>$catName]]);
            } else {
                $html .= '<option value="">Please select level 1 category</option>';
                $response = $this->helper->ApiObject()->findAllTopCategoryChildren(['params' => ['tag'=> $catName]]);
            }
            if (isset($response['results'])) {
                $options = $response['results'];
                foreach ($options as $value) {
                    $html .= "<option value='".$value['category_id'].".".$value['name']."'>".$value['short_name']."</option>";
                }
            }
        }
        $this->getResponse()->setBody($html);
    }  
}
