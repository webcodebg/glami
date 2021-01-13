<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Webcode\Glami\Helper\Data;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class FeedUrl extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\View\Helper\SecureHtmlRenderer|mixed
     */
    private $secureRenderer;

    /**
     * @var \Webcode\Glami\Helper\Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * Render scope label
     *
     * @param AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _renderScopeLabel(AbstractElement $element): ?string
    {
        return null;
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->helper->getFeedUrl();
    }
}
