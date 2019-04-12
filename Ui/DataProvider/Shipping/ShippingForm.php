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
namespace Ced\Etsy\Ui\DataProvider\Shipping;

/**
 * Class ProductDataProvider
 */
class ShippingForm extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public  $objectManager;
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    public $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    public $addFilterStrategies;

    /**
     * DataProvider constructor.
     *
     * @param string                                    $name
     * @param string                                    $primaryFieldName
     * @param string                                    $requestFieldName
     * @param Ced\Etsy\Model\shipping                     $collectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array                                     $addFieldStrategies
     * @param array                                     $addFilterStrategies
     * @param array                                     $meta
     * @param array                                     $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\Etsy\Model\Shipping $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_objectmanager = $objectManager;
        $this->collection= $collectionFactory->getCollection();
        $this->size = sizeof($this->collection->getData());
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data=[];
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        $items = $this->getCollection()->getData();

        foreach ($items as $item) {
            $data[$item['id']] = $item;
        }
        return $data;
    }
}