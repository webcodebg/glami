<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Block\Adminhtml\System\Config;

use Magento\Catalog\Model\CategoryList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\Xml\Parser;
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
     * @return bool
     */
    public function canRenderCategories()
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
    protected function _toHtml()
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
