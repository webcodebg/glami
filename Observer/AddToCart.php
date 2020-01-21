<?php
/**
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2020 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Observer;

use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Webcode\Glami\Helper\Data;

/**
 * Class AddToCart
 * @package Webcode\Glami\Observer
 */
class AddToCart implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Webcode\Glami\Helper\Data
     */
    protected $helper;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * ProductFactory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * AddToCart constructor.
     *
     * @param ProductFactory $productFactory
     * @param Data $helper
     */
    public function __construct(
        ProductFactory $productFactory,
        Data $helper
    ) {
        $this->productFactory = $productFactory;
        $this->helper         = $helper;
    }

    /**
     * Catch add to cart event
     *
     * @param Observer $observer
     *
     * @return $this
     * @throws Exception
     */
    public function execute(Observer $observer)
    {

        if ($this->helper->isEnabled()) {
            $product = $observer->getData('product');
            $request = $observer->getData('request');

            $qty = $request->getParam('qty');
            if ($product->getTypeId() == "configurable") {
                $selectedProduct = $this->productFactory->create();
                $selectedProduct->load($selectedProduct->getIdBySku($product->getSku()));
                // $this->helper->getSessionManager()->setAddToCartData($this->helper->getAddToCartData($selectedProduct, $qty));
            } else {
                // $this->helper->getSessionManager()->setAddToCartData($this->helper->getAddToCartData($product, $qty));
            }
            var_dump(1111);
        }
        return $this;
    }
}