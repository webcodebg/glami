<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Cron;

class GenerateFeed
{
    /**
     * @var \Webcode\Glami\Service\GenerateFeed
     */
    private $generateFeed;

    public function __construct(\Webcode\Glami\Service\GenerateFeed $generateFeed)
    {
        $this->generateFeed = $generateFeed;
    }

    /**
     * Cronjob Description
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $this->generateFeed->execute();
    }
}
