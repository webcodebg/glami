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
     * Name of the event.
     *
     * @var $this
     */
    public $eventName = null;

    /**
     * Data for the fired event.
     *
     * @var array
     */
    public $eventData;

    /**
     * Helper class
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
     * @param string $name
     */
    public function setEventName(string $name)
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
     * @return string
     */
    public function getLocale(): string
    {
        return $this->helper->getPixelLocale();
    }

    /**
     * Check is Pixel Enabled.
     *
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

    /**
     * Get Contest Parameter.
     *
     * @return int
     */
    public function getCookieConsent(): int
    {
        return 1;
    }
}
