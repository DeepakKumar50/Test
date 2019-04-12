<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 12/5/18
 * Time: 4:14 PM
 */

namespace Ced\Etsy\Ui\Component\Listing\Columns\Product;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Ced\Etsy\Model\Profile;
class ProfileId extends Column
{
    const URL_PATH_EDIT = 'etsy/profile/edit';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    public $profile;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Profile $profile,
        array $components = [],
        array $data = []
    )
    {
        $this->_urlBuilder = $urlBuilder;
        $this->profile=$profile;
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

                $profile = $this->profile
                    ->load($item['profile_id']);
                $item[$this->getData('name')] = [
                    'edit' => [
                        'label' => __($profile->getProfileName()),
                        'href' => $this->_urlBuilder->getUrl(
                            static::URL_PATH_EDIT,
                            [
                                'pcode' =>   $profile->getprofile_code()
                            ]
                        ),
                        'hidden' => false,
                        'target' => "_blank",
                    ]
                ];

            }
        }
        return $dataSource;
    }
}