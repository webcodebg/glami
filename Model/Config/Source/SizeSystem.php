<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webcode\Glami\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * StockQuantity type model
 *
 */
class SizeSystem implements OptionSourceInterface
{
    /**
     * Get product type labels array with empty value
     *
     * @return array
     */
    public function getAllOption(): array
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);

        return $options;
    }

    /**
     * Get product type labels array
     *
     * @return array
     */
    public function getOptionArray(): array
    {
        return [
            '' => '',
            'INT' => __('International (Ex.: XS, S, M, L, XL, XXL)'),
            'EU' => __('European (Ex.: EU 13, EU 14, EU 15 ...)'),
            'AU' => __('AU'),
            'BR' => __('BR'),
            'CN' => __('CN'),
            'DE' => __('DE'),
            'FR' => __('FR'),
            'IT' => __('IT (Ex.: IT 36, IT 37, IT 38 ...)'),
            'JP' => __('JP'),
            'MEX' => __('MEX'),
            'RU' => __('RU (Ex.: RU 16, RU 17, RU 18 ...)'),
            'UK' => __('UK (Ex.: UK 0, UK 2, UK 2.5 ...)'),
            'US' => __('US (Ex.: US 0, US 2, US 2.5 ...)'),
        ];
    }

    /**
     * Get product type labels array with empty value for option element
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);

        return $res;
    }

    /**
     * Get product type labels array for option element
     *
     * @return array
     */
    public function getOptions(): array
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return $this->getOptions();
    }
}
