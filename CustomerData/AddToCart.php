<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webcode\Glami\CustomerData;

use Webcode\Glami\Model\Session;
use Magento\Customer\CustomerData\SectionSourceInterface;

class AddToCart implements SectionSourceInterface
{
    /**
     * @var Session
     */
    protected $glamiSession;

    /**
     * @param \Webcode\Glami\Model\Session $glamiSession
     */
    public function __construct(Session $glamiSession)
    {
        $this->glamiSession = $glamiSession;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData(): array
    {
        $data = [];
        if ($this->glamiSession->hasAddToCartData()) {
            $data = $this->glamiSession->getAddToCartData();
            $this->glamiSession->unsAddToCartData();
        }

        return $data;
    }
}
