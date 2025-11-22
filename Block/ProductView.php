<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webcode\Glami\Block;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Webcode\Glami\Helper\Data as HelperData;

/**
 * StockQuantity Information to view
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
     * Constructor.
     *
     * @param HelperData $helper
     * @param StoreManagerInterface $storeManager
     * @param Session $catalogSession
     * @param ProductRepositoryInterface $productRepository
     * @param Json $json
     * @param Context $context
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(
        HelperData $helper,
        StoreManagerInterface $storeManager,
        Session $catalogSession,
        ProductRepositoryInterface $productRepository,
        Json $json,
        Context $context,
        array $data = []
    ) {
        $this->catalogSession = $catalogSession;
        $this->productRepository = $productRepository;
        $this->helper = $helper;

        $this->setEventName('ViewContent');
        $this->assignEventData();
        parent::__construct($helper, $storeManager, $json, $context, $data);
    }

    /**
     * Get product detail info.
     *
     * @throws Exception
     */
    public function assignEventData(): void
    {
        $itemIds = [];

        if ($this->getCurrentProduct()) {
            $itemIds[] = $this->currentProduct->getSku();
        }

        // TODO: Add Child Products also.

        $this->eventData = [
            'item_ids' => $itemIds,
            'content_type' => 'product',
            'consent' => $this->getCookieConsent()
        ];
    }

    /**
     * Get current product from session.
     *
     * @return ProductInterface|bool
     */
    public function getCurrentProduct()
    {
        if (!$this->currentProduct && ($productId = $this->getProductId())) {
            try {
                $this->currentProduct = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                $this->_logger->alert($this->helper::MODULE_NAME, ['message' => $e->getMessage()]);

                return false;
            }
        }

        return $this->currentProduct;
    }

    /**
     * Get StockQuantity ID from session.
     *
     * @return int|null
     */
    private function getProductId(): ?int
    {
        if (!$this->productId && !$this->productId = $this->catalogSession->getData('last_viewed_product_id')) {
            return null;
        }

        return (int) $this->productId;
    }
}
