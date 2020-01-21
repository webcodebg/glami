<?php
/**
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2020 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Helper;

use Magento\Store\Model\StoreManager;

/**
 * Class Data
 * @package Webcode\Glami\Helper
 */
class Data extends \Webcode\Core\Helper\Data
{
    /**
     * Path to Config for Pixel ID
     */
    const XML_PATH_FACEBOOK_PIXEL_ID = 'general/pixel_id';

    /**
     * @var string Module Name
     */
    protected $moduleName = 'glami';

    /**
     * Format Product Price. Convert currency and add currency label.
     *
     * @param float $price
     * @param StoreManager $store
     *
     * @return string
     */
    public function formatPrice($price, StoreManager $store)
    {
        $baseCurrencyCode    = $store->getBaseCurrencyCode();
        $currentCurrencyCode = $store->getCurrentCurrencyCode();

        if ($baseCurrencyCode !== $currentCurrencyCode) {
            $price = $store->getBaseCurrency()->convert($price, $currentCurrencyCode);
        }

        return number_format($price, 2) . ' ' . $currentCurrencyCode;
    }

    /**
     * @return string
     */
    public function getPixelId()
    {
        try {
            if ($this->isEnabled()) {
                return $this->getConfigData('general/pixel_id');
            }
        } catch (\Exception $e) {
            $this->_logger->alert($this->moduleName, ['message' => $e->getMessage()]);
        }

        return false;
    }
}
