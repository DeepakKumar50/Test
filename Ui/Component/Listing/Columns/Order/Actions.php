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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Etsy\Ui\Component\Listing\Columns\Order;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductActions
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $salesOrder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param \Magento\Sales\Model\Order $salesOrder
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        \Magento\Sales\Model\Order $salesOrder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->salesOrder = $salesOrder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if($item['magento_order_id'] != 'N/A') {
                    $this->salesOrder->loadByIncrementId($item['magento_order_id']);
                    $item[$this->getData('name')]['view'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'sales/order/view',
                            ['etsy_order_id' => $this->salesOrder->getId()]
                        ),
                        'target' => '_blank',
                        'label' => __('View'),
                        'class' => 'cedcommerce actions view',
                        'hidden' => false,
                    ];
                }

                $item[$this->getData('name')]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'etsy/order/massDeleteOrders',
                        ['id' => $item['id']]
                    ),
                    'label' => __('Delete'),
                    'class' => 'cedcommerce actions delete',
                    'hidden' => false,
                ];


                $item[$this->getData('name')]['sync'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'etsy/order/syncfailedorder',
                        ['id' => $item['id']]
                    ),
                    'label' => __('Sync '),
                    'class' => 'cedcommerce actions sync',
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}