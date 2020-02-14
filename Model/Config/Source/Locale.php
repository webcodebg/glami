<?php
/**
 * @package      Sync
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright (c) 2020 Webcode Ltd. (https://webcode.bg/)
 * @license      Academic Free License (AFL 3.0)
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
            'ro' => __('Romanian'),
            'en' => __('English')
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
