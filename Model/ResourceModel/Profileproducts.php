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

namespace Ced\Etsy\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Profileproducts
 *
 * @package Ced\Etsy\Model\ResourceModel
 */
class Profileproducts extends AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('etsy_profile_products', 'id');
    }

    /**
     * @param AbstractModel $profile
     * @return $this
     */
    protected function _beforeSave(AbstractModel $profile)
    {
        if ($profile->getId() == '') {
            if ($profile->getIdFieldName()) {
                $profile->unsetData($profile->getIdFieldName());
            } else {
                $profile->unsetData('id');
            }
        }

        $profile->setProfileName($profile->getName());
        return $this;
    }

    /**
     * @param $profileId
     * @return array
     */
    public function getProfileProducts($profileId)
    {
        $read = $this->getConnection();
        $select = $read->select()->from($this->getMainTable(), ['product_id'])->where(
            "(profile_id = '{$profileId}' ) AND 
        product_id > 0"
        );
        return $read->fetchCol($select);
    }

    /**
     * @param $productId
     * @return $this
     */
    public function deleteFromProfile($productId)
    {
        if ($productId <= 0) {
            return $this;
        }
        $dbh = $this->getConnection();
        $condition = "{$this->getTable('etsy_profile_products')}.product_id = " . $dbh->quote($productId);
        $dbh->delete($this->getTable('etsy_profile_products'), $condition);
        return $this;

    }

    /**
     * @param $productId
     * @param $profileId
     * @return array
     */
    public function profileProductExists($productId, $profileId)
    {
        if ($productId > 0) {
            $profileTable = $this->getTable('etsy_profile_products');

            $productProfile = \Magento\Framework\App\ObjectManager::getInstance()->get('Ced\Etsy\Model\Profileproducts')->loadByField('profile_id', $profileId);
            if ($productProfile && $productProfile->getId()) {
                $dbh = $this->getConnection();
                $select = $dbh->select()->from($profileTable)->where("product_id = {$productId} AND profile_id = {$profileId}");
                return $dbh->fetchCol($select);
            } else {
                return [];
            }
        } else {
            return [];
        }
    }
}

