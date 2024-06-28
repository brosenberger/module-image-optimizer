<?php
declare(strict_types=1);

namespace BroCode\ImageOptimizer\Observer;

use BroCode\ImageOptimizer\Api\Data\ImageConverterInterface;
use BroCode\ImageOptimizer\Model\ImageConverterService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Dispatcher for the `brocode_convert_image` event.
 */
class InstantConvertImageObserver implements ObserverInterface
{
    /**
     * @var ImageConverterInterface[]
     */
    private array $imageConverter = [];
    /**
     * @var LoggerInterface
     */
    private $logger;
    private ImageConverterService $imageConverterService;

    /**
     * @param ImageConverterInterface[] $imageConverter
     */
    public function __construct(
        LoggerInterface $logger,
        ImageConverterService $imageConverterService
    ) {
        $this->logger = $logger;
        $this->imageConverterService = $imageConverterService;
    }

    /**
     * Handle the `brocode_convert_image` event.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $converter = $this->imageConverterService->getImageConverter($observer->getConverterId());
        if ($converter == null) {
            $this->logger->critical('BroCode - ImageOptimizer: Converter ' . $observer->getConverterId() . ' not configured.');
            return;
        }

        $this->doConvert($converter, $observer->getImagePath());
    }

    protected function doConvert($imageConverter, $imagePath)
    {
        $imageConverter->convert($imagePath);
    }
}
