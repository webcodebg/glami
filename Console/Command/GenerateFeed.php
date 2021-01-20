<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webcode\Glami\Service\GenerateFeed as GenerateFeedService;

class GenerateFeed extends Command
{
    /**
     * @var ProgressBarFactory
     */
    private $progressBarFactory;

    /**
     * @var GenerateFeedService
     */
    private $generateFeedService;

    /**
     * @var State
     */
    private $state;

    /**
     * GenerateFeed constructor.
     *
     * @param \Symfony\Component\Console\Helper\ProgressBarFactory $progressBarFactory
     * @param \Webcode\Glami\Service\GenerateFeed $generateFeedService
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        ProgressBarFactory $progressBarFactory,
        GenerateFeedService $generateFeedService,
        State $state
    ) {
        $this->progressBarFactory = $progressBarFactory;
        $this->generateFeedService = $generateFeedService;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('glami:feed:generate');
        $this->setDescription('Generate Glami Feed for Store');
        parent::configure();
    }

    /**
     * CLI command description
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (LocalizedException $e) {
            $output->writeln($e->getMessage());

            return Cli::RETURN_FAILURE;
        }

        $progressBar = $this->progressBarFactory->create(['output' => $output]);
        $progressBar->start();
        $this->generateFeedService->setProgressBar($progressBar);
        $this->generateFeedService->execute();

        $output->write(PHP_EOL);
        return Cli::RETURN_SUCCESS;
    }
}
