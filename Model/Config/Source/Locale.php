<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Product type model
 *
 */
class Locale implements OptionSourceInterface
{
    /**
     * Get product type labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
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
    public function getOptionArray()
    {
        return [
            'bg' => __('Bulgarian'),
            'cz' => __('Czech'),
            'hr' => __('Croatian'),
            'eco' => __('English'),
            'fr' => __('Frensh'),
            'de' => __('German'),
            'gr' => __('Greek'),
            'hu' => __('Hungarian'),
            'pt' => __('Portuguese'),
            'ro' => __('Romanian'),
            'ru' => __('Russian'),
            'sk' => __('Slovak'),
            'si' => __('Slovenian'),
            'es' => __('Spanish'),
            'tr' => __('Turkish'),
            'ee' => __('Estonian'),
            'lv' => __('Latvian'),
            'lt' => __('Lithuanian'),
            'br' => __('Brasilian')
        ];
    }

    /**
     * Get product type labels array with empty value for option element
     *
     * @return array
     */
    public function getAllOptions()
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
    public function getOptions()
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
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
