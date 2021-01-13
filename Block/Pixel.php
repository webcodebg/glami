<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Block;

use Exception;
use Magento\Framework\Serialize\Serializer\Json;
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
     * JSON Serializer
     *
     * @var Json
     */
    protected $json;

    /**
     * Constructor.
     *
     * @param HelperData $helper
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        HelperData $helper,
        StoreManagerInterface $storeManager,
        Json $json,
        Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->json = $json;
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
        return $this->json->serialize($this->eventData);
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
     * @return string|null
     */
    public function getLocale()
    {
        return $this->helper->getPixelLocale();
    }

    /**
     * @return bool
     */
    public function isPixelEnabled(): bool
    {
        try {
            return $this->helper->isActive();
        } catch (Exception $e) {
            return false;
        }
    }
}
