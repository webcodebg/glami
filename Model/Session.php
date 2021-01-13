<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Model;

use Magento\Framework\Session\SessionManager;

class Session extends SessionManager
{
    /**
     * @param $data
     *
     * @return $this
     */
    public function setAddToCartData($data)
    {
        $this->setData('add_to_cart', $data);

        return $this;
    }

    /**
     * Get AddToCart Data
     *
     * @return mixed|null
     */
    public function getAddToCartData()
    {
        if ($this->hasAddToCartData()) {
            $data = $this->getData('add_to_cart');
            $this->unsAddToCartData();

            return $data;
        }

        return null;
    }

    /**
     * Check AddToCart Data
     *
     * @return bool
     */
    public function hasAddToCartData()
    {
        return $this->hasData('add_to_cart');
    }

    /**
     * Unset AddToCart
     */
    public function unsAddToCartData()
    {
        $this->unsetData('add_to_cart');
    }
}
