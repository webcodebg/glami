<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Block;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Encoder;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Webcode\Glami\Helper\Data as HelperData;

/**
 * Product Information to view
 */
class ProductView extends Pixel
{

    /**
     * @var Session
     */
    private $catalogSession;

    /**
     * @var ProductInterface|bool
     */
    private $currentProduct;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Constructor.
     *
     * @param HelperData $helper
     * @param StoreManagerInterface $storeManager
     * @param Session $catalogSession
     * @param ProductRepositoryInterface $productRepository
     * @param Encoder $jsonEncoder
     * @param Context $context
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(
        HelperData $helper,
        StoreManagerInterface $storeManager,
        Session $catalogSession,
        \Magento\Framework\Registry $registry,
        ProductRepositoryInterface $productRepository,
        Encoder $jsonEncoder,
        Context $context,
        array $data = []
    ) {
        $this->catalogSession = $catalogSession;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->registry = $registry;

        $this->setEventName('ViewContent');
        $this->assignEventData();
        parent::__construct($helper, $storeManager, $jsonEncoder, $context, $data);
    }

    /**
     * Get product detail info
     * @throws Exception
     */
    public function assignEventData()
    {
        $itemIds = [];

        if ($currentProduct = $this->getCurrentProduct()) {
            $itemIds[] = $currentProduct->getSku();
        }

        // TODO: Add Child Products also.

        $this->eventData = [
            'item_ids' => $itemIds,
            'content_type' => 'product'
        ];
    }

    /**
     * @return ProductInterface|bool
     */
    public function getCurrentProduct()
    {
        if (!$this->currentProduct) {
            try {
                $this->currentProduct = $this->registry->registry('current_product');
            } catch (Exception $e) {
                $this->helper->logger($e->getMessage());

                return false;
            }
        }

        return $this->currentProduct;
    }
}
