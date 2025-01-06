<?php

namespace BroCode\ImageOptimizer\Command;

use BroCode\ImageOptimizer\Model\ImagePathScannerService;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageScanPathsCommand extends Command
{

    private ImagePathScannerService $imagePathScannerService;

    public function __construct(
        ImagePathScannerService $imagePathScannerService,
        ?string $name = null)
    {
        parent::__construct($name);
        $this->imagePathScannerService = $imagePathScannerService;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configure()
    {
        $this->setName('images:optimize:scan')
            ->setDescription('Initiates a path scan for images to be optimized, depending on the configuration the images are converted directly or via queue.')
            ->addOption(
                'list',
                '-l',
                InputOption::VALUE_NONE,
                'Lists all exportable paths and their optimization target, stored under var/export'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $listing = $input->getOption('list');

        try {
            $this->imagePathScannerService->iteratePaths($listing ? '_listing' : '');

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('Failed to execute image scan for optimizations');
            return Cli::RETURN_FAILURE;
        }
    }
}
