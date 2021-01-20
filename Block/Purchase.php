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
use Magento\Checkout\Model\Session;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Json\Encoder;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Webcode\Glami\Helper\Data as HelperData;

/**
 * Purchase Information to view
 */
class Purchase extends Pixel
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param Session $checkoutSession
     * @param HelperData $helper
     * @param StoreManagerInterface $storeManager
     * @param Encoder $jsonEncoder
     * @param Context $context
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(
        Session $checkoutSession,
        HelperData $helper,
        StoreManagerInterface $storeManager,
        Encoder $jsonEncoder,
        Context $context,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper          = $helper;

        $this->setEventName('Purchase');
        $this->assignEventData();
        parent::__construct($helper, $storeManager, $jsonEncoder, $context, $data);
    }

    /**
     * Get product detail info
     * @throws Exception
     */
    public function assignEventData()
    {
        $order        = $this->checkoutSession->getLastRealOrder();
        $itemIds      = [];
        $productNames = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $itemIds[]      = $item->getSku();
            $productNames[] = $item->getName();
        }

        $this->eventData = [
            'item_ids'       => $itemIds,
            'product_names'  => $productNames,
            'value'          => $this->helper->formatPrice($order->getGrandTotal(), false),
            'currency'       => $this->helper->getCurrentStoreCurrency(),
            'transaction_id' => $order->getIncrementId()
        ];
    }
}
