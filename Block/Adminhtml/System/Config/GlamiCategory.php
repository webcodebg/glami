<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webcode\Glami\Block\Adminhtml\System\Config;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Webcode\Glami\Helper\Data;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class GlamiCategory extends Select
{
    /**
     * @var \Webcode\Glami\Helper\Data
     */
    private $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webcode\Glami\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check if can render Glami categories.
     *
     * @return bool
     */
    public function canRenderCategories(): bool
    {
        if ($this->helper->getGlamiCategories()) {
            return true;
        }

        return false;
    }

    /**
     * Render block HTML
     *
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _toHtml(): string
    {
        $this->setId($this->getData('input_id'));
        $this->setData('name', $this->getData('input_name'));

        if (!$this->getOptions()) {
            $options = [];
            foreach ($this->helper->getGlamiCategories() as $value => $label) {
                $options[] = compact('label', 'value');
            }
            $this->setOptions($options);
        }

        return parent::_toHtml();
    }
}
