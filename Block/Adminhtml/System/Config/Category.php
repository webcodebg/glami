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

use Magento\Catalog\Model\CategoryList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Category extends Select
{
    /**
     * @var \Magento\Catalog\Model\CategoryList
     */
    protected $categoryList;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Model\CategoryList $categoryList
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        CategoryList $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->categoryList = $categoryList;
    }

    /**
     * Get list of Categories.
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface[]
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getCategoryList(): array
    {
        $sortOrders[] = $this->sortOrderBuilder->create()->setField('path')->setDirection('ASC');
        $sortOrders[] = $this->sortOrderBuilder->create()->setField('position')->setDirection('ASC');

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setSortOrders($sortOrders);

        return $this->categoryList->getList($searchCriteria)->getItems();
    }
}
