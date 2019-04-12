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

namespace Ced\Etsy\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class JetProduct
 */
class EtsyProduct extends AbstractDataProvider
{
    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
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

    public $filterBuilder;

    public $objectManager;

    /**
     * JetProduct constructor.
     *
     * @param string                                    $name
     * @param string                                    $primaryFieldName
     * @param string                                    $requestFieldName
     * @param CollectionFactory                         $collectionFactory
     * @param FilterBuilder                             $filterBuilder
     * @param ObjectManagerInterface $objectManager
     * @param array                                     $addFieldStrategies
     * @param array                                     $addFilterStrategies
     * @param array                                     $meta
     * @param array                                     $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        ObjectManagerInterface $objectManager,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->filterBuilder = $filterBuilder;
        $this->objectManager = $objectManager;
        $ids = $this->objectManager->create('Ced\Etsy\Model\Profileproducts')->getCollection()->getColumnValues('product_id');
        $dumy_collection = $collectionFactory->create();
        $dumy_collection->joinField('category_id', 'catalog_category_product', 'category_id', 'product_id = entity_id', null);

        $this->collection = $collectionFactory->create();
        $this->collection->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id = entity_id', '{{table}}.stock_id=1', null);
        $this->collection->joinField('profile_id', 'etsy_profile_products','profile_id', 'product_id = entity_id', null);
        $this->collection->joinField('profile_status', 'etsy_profile','profile_status', 'id = profile_id', null);
        $this->addField('etsy_product_status');
        $this->addField('etsy_listing_id');
        $this->collection->addAttributeToSelect('profile_id');
        $this->addFilter(
            $this->filterBuilder->setField('entity_id')->setConditionType('in')
                ->setValue($ids)
                ->create()
        );
        $this->addFilter(
            $this->filterBuilder->setField('profile_status')->setConditionType('in')
                ->setValue([1])
                ->create()
        );
        $this->addFilter(
            $this->filterBuilder->setField('type_id')->setConditionType('in')
                ->setValue(['simple','configurable'])
                ->create()
        );
         $this->addFilter(
             $this->filterBuilder->setField('visibility')->setConditionType('in')
                 ->setValue([2,3,4])
                 ->create()
         );
         $this->addFieldStrategies = $addFieldStrategies;
         $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * Add field to select
     *
     * @param  string|array $field
     * @param  string|null  $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }
}
