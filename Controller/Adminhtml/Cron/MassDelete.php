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
 * @category    Ced
 * @package     Ced_Etsy
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Controller\Adminhtml\Cron;

/**
 * Class MassDelete
 * @package Ced\Etsy\Controller\Adminhtml\Cron
 */
class MassDelete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ced_Etsy::Etsy';

    public function execute()
    {
        $schedulerIds = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded', false);
        if (!is_array($schedulerIds) && !$excluded) {
            $this->messageManager->addErrorMessage(__('Please select Scheduler(s).'));
        } else if ($excluded == "false") {
            $schedulerIds = $this->_objectManager->create('Ced\Etsy\Model\CronScheduler')->getCollection()->getAllIds();
        }

        if (!empty($schedulerIds)) {
            try {
                foreach ($schedulerIds as $profileId) {
                    $profile = $this->_objectManager->create('Ced\Etsy\Model\CronScheduler')->load($profileId);
                    $profile->delete();
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been deleted.', count($schedulerIds)));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('*/*/scheduler');
    }
}
