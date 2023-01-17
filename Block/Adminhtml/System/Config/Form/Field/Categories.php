<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Webcode\Glami\Block\Adminhtml\System\Config\Category;
use Webcode\Glami\Block\Adminhtml\System\Config\GlamiCategory;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Categories extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Webcode_Glami::system/config/form/field/array.phtml';

    /**
     * Categories Renderer Block.
     *
     * @return \Magento\Framework\View\Element\BlockInterface|object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryRenderer()
    {
        return $this->getLayout()->createBlock(Category::class);
    }

    /**
     * Single Category Renderer Block.
     *
     * @return \Magento\Framework\View\Element\BlockInterface|object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGlamiCategoryRenderer()
    {
        return $this->getLayout()->createBlock(GlamiCategory::class);
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of \Magento\Framework\DataObject
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function getArrayRows(): array
    {
        $arrayRows = parent::getArrayRows();

        $categories = [];
        $path = '';
        foreach ($this->getCategoryRenderer()->getCategoryList() as $category) {
            if ($category->getLevel() > 1) {
                if ($category->getLevel() > 2) {
                    $path .= ' > ';
                } else {
                    $path = '';
                }

                $path .= $category->getName();
            }

            $categories[$category->getId()] = $path;
        }

        foreach ($arrayRows as $arrayRow) {
            if (isset($categories[$arrayRow->getSourceId()])) {
                unset($categories[$arrayRow->getSourceId()]);
            }
        }

        foreach ($categories as $categoryId => $categoryName) {
            if (!empty($categoryName)) {
                $arrayRow = new DataObject();
                $rowId = time() . '_' . $categoryId;
                $data = [
                    '_id' => $rowId,
                    'source_id' => $categoryId,
                    'source' => $categoryName,
                    'column_values' => [
                        $rowId . '_source_id' => $categoryId,
                        $rowId . '_source' => $categoryName,
                    ],
                ];
                $arrayRow->setData($data);
                $arrayRows[] = $arrayRow;
            }
        }

        return $arrayRows;
    }

    /**
     * Add Renreder
     *
     * @return void
     */
    protected function _prepareToRender(): void
    {
        try {
            $this->addColumn('source', ['label' => __('Magento')]);
            $this->addColumn('target', ['label' => __('Glami'), 'renderer' => $this->getGlamiCategoryRenderer()]);
        } catch (LocalizedException $e) {
            throw new \RuntimeException($e);
        }
        $this->_construct();
    }
}
