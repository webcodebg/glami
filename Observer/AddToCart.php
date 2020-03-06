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
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Webcode\Glami\Helper\Data;
use Webcode\Glami\Model\Session;

/**
 * Class AddToCart used to hold data from product added to cart and fired with js after that.
 */
class AddToCart implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $glamiSession;

    /**
     * ProductFactory
     *
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * Helper
     *
     * @var Data
     */
    protected $helper;

    /**
     * AddToCart constructor.
     *
     * @param Session $glamiSession
     * @param ProductFactory $productFactory
     * @param Data $helper
     */
    public function __construct(
        Session $glamiSession,
        ProductFactory $productFactory,
        Configurable $configurable,
        Data $helper
    ) {
        $this->glamiSession   = $glamiSession;
        $this->productFactory = $productFactory;
        $this->configurable = $configurable;
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

            if ($product->getTypeId() == "configurable") {
                $product = $this->configurable->getProductByAttributes($request->getParam('super_attribute'), $product);
            }

            $this->glamiSession->setAddToCartData([
                'item_ids'   => [$product->getId()],
                'product_names' => [$product->getName()],
                'value' => $this->helper->formatPrice($product->getFinalPrice(), false),
                'currency' => $this->helper->getCurrentStoreCurrency(),
            ]);
        }

        return $this;
    }
}
