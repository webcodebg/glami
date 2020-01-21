<?php
/**
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2020 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Block;

use Exception;
use Magento\Cookie\Helper\Cookie as CookieHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Webcode\Glami\Helper\Data as HelperData;

/**
 * Class Pixel
 * @package Webcode\Glami\Block
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pixel extends Template
{
    /**
     * Set EventData
     *
     * @var $this
     */
    public $eventData;

    /**
     * HelperCookie
     *
     * @var CookieHelper
     */
    protected $cookieHelper;

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
        $this->helper       = $helper;
        $this->cookieHelper = $cookieHelper;
        $this->json         = $json;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get EventName
     *
     * @return string
     */
    public function getEventName()
    {
        try {
            return (new \ReflectionClass($this))->getShortName();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
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
}
