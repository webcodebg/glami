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
use Magento\Framework\Json\Encoder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Webcode\Glami\Helper\Data as HelperData;

/**
 * Pixel Block
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pixel extends Template
{
    /**
     * Event Name
     *
     * @var $this
     */
    public $eventName = null;

    /**
     * EventData
     *
     * @var array
     */
    public $eventData;

    /**
     * HelperData
     *
     * @var HelperData
     */
    protected $helper;

    /**
     * Store Manager Interface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Encoder
     */
    private $jsonEncoder;

    /**
     * Constructor.
     *
     * @param HelperData $helper
     * @param StoreManagerInterface $storeManager
     * @param Encoder $jsonEncoder
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        HelperData $helper,
        StoreManagerInterface $storeManager,
        Encoder $jsonEncoder,
        Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Set EventName
     *
     * @param $name
     */
    public function setEventName($name)
    {
        $this->eventName = $name;
    }

    /**
     * Get EventName
     *
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * Get EventName
     *
     * @return string
     */
    public function getEventData()
    {
        return $this->jsonEncoder->encode($this->eventData);
    }

    /**
     * Get Pixel ID
     *
     * @return int
     */
    public function getPixelId()
    {
        return $this->helper->getPixelId();
    }

    /**
     * Get Pixel Locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->helper->getPixelLocale();
    }

    /**
     * @return bool
     */
    public function isPixelEnabled()
    {
        try {
            return $this->helper->isActive();
        } catch (Exception $e) {
            $this->helper->logger($e->getMessage());
            return false;
        }
    }
}
