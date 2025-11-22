<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webcode\Glami\Console\Command;

use Exception;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webcode\Glami\Service\GenerateFeed as GenerateFeedService;

class GenerateFeed extends Command
{
    /**
     * GenerateFeed constructor.
     *
     * @param ProgressBarFactory $progressBarFactory
     * @param GenerateFeedService $generateFeedService
     */
    public function __construct(
        private ProgressBarFactory $progressBarFactory,
        private GenerateFeedService $generateFeedService
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
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
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(__('<info>%1</info>', 'Start Generating Glami Feeds.'));

        $progressBar = $this->progressBarFactory->create(['output' => $output]);
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% | %message%');
        $progressBar->setMessage('Preparing...');

        $this->generateFeedService->setProgressBar($progressBar);
        $this->generateFeedService->execute();

        $output->write(PHP_EOL);
        $output->writeln(__('<info>%1</info>', 'Glami Feed was generated.'));

        return Cli::RETURN_SUCCESS;
    }
}
