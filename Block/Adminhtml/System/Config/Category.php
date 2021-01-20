<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Block\Adminhtml\System\Config;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Category extends Select
{
    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $categoryHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryHelper = $categoryHelper;
    }

    /**
     */
    public function getCategoryList()
    {
        return $this->categoryHelper->getStoreCategories();
    }
}
