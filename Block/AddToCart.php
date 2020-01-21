<?php
/**
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2020 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Block;

use Magento\Cookie\Helper\Cookie as CookieHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Webcode\Glami\Helper\Data as HelperData;

/**
 * Class Pixel
 * @package Webcode\Glami\Block
 */
class AddToCart extends Pixel
{
    /**
     * Constructor.
     *
     * @param CookieHelper $cookieHelper
     * @param HelperData $helper
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CookieHelper $cookieHelper,
        HelperData $helper,
        StoreManagerInterface $storeManager,
        Json $json,
        Context $context,
        array $data = []
    ) {
        parent::__construct($cookieHelper, $helper, $storeManager, $json, $context, $data);
        $this->setEventData();
    }

    /**
     * Get product detail info
     */
    public function setEventData()
    {
        $this->eventData = ['content_ids' => [1, 2, 3, 4]];
//        $currentProduct = $this->_helper->getGtmRegistry()->registry('product');
//        $data['dynamic_remarketing_retail'] = [
//            'ecomm_prodid'     => $currentProduct->getSku(),
//            'ecomm_pagetype'   => 'product',
//            'ecomm_totalvalue' => (float)number_format($currentProduct->getFinalPrice(), 2)
//        ];
    }

}