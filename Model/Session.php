<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webcode\Glami\Model;

use Magento\Framework\Session\SessionManager;

class Session extends SessionManager
{
    /**
     * Set data in session for products added to cart
     *
     * @param array $data
     *
     * @return $this
     */
    public function setAddToCartData(array $data): Session
    {
        $this->setData('add_to_cart', $data);

        return $this;
    }

    /**
     * Get AddToCart Data from session.
     *
     * @return mixed|null
     */
    public function getAddToCartData(): ?array
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
    public function hasAddToCartData(): bool
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
