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

namespace Ced\Etsy\Helper;

/**
 * Class Cache
 *
 * @package Ced\Etsy\Helper
 */
class Cache extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Cache
     */
    protected $cache;

    /**
     * Cache key identifier
     */
    const CACHE_KEY_IDENTIFIER = 'ced_etsy_';

    const PROFILE_CACHE_KEY = 'profile_';

    const PROFILE_PRODUCT_CACHE_KEY = 'profile_product_';

    /**
     * Cache constructor.
     *
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Helper\Context $context
    ) {
    
        $this->cache = $cache;
        parent::__construct($context);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getValue($key)
    {
        $cacheKey = self::CACHE_KEY_IDENTIFIER.'_'.$key;
        $value = $this->cache->load($cacheKey);
        return $value === false ? null : unserialize($value);
    }

    /**
     * @param $key
     * @param $value
     * @param array $tags
     * @param null  $lifeTime
     * @throws \Exception
     */
    public function setValue($key, $value, array $tags = [], $lifeTime = null)
    {
        if ($value === null) {
            throw new \Exception('Null value not allowed');
        }

        if ($lifeTime === null || (int)$lifeTime <= 0) {
            $lifeTime = 60*60*24*365*5;
        }

        $cacheKey = self::CACHE_KEY_IDENTIFIER.'_'.$key;

        $preparedTags = [self::CACHE_KEY_IDENTIFIER.'_main'];
        foreach ($tags as $tag) {
            $preparedTags[] = self::CACHE_KEY_IDENTIFIER.'_'.$tag;
        }

        $this->cache->save(serialize($value), $cacheKey, $preparedTags, (int)$lifeTime);
    }

    /**
     * @param $key
     */
    public function removeValue($key)
    {
        $cacheKey = self::CACHE_KEY_IDENTIFIER.'_'.$key;
        $this->cache->remove($cacheKey);
    }

    public function removeTagValues($tag)
    {
        $tags = [self::CACHE_KEY_IDENTIFIER.'_'.$tag];
        $this->cache->clean($tags);
    }

    public function removeAllValues()
    {
        $this->removeTagValues('main');
    }
}
