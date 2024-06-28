<?php

namespace BroCode\ImageOptimizer\Model;

use BroCode\ImageOptimizer\Api\Data\ImageConvertValidationInterface;
use Magento\Framework\Event\Manager;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ImagePathScannerService
{
    /**
     * @var string[]
     */
    private array $imageExtensions;

    /**
     * @var Manager
     */
    private $eventManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ImageConverterService
     */
    private $imageConverterService;

    /**
     * @param string[] $imageExtensions
     * @param ImageConvertValidationInterface[] $imageConvertValidators
     */
    public function __construct(
        LoggerInterface $logger,
        Manager $eventManager,
        ImageConverterService $imageConverterService,
        $imageExtensions = []
    ) {
        $this->imageExtensions = $imageExtensions;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->imageConverterService = $imageConverterService;
    }

    public function scanPath($directory)
    {
        try {
            $iterator = new \RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory),
                RecursiveIteratorIterator::LEAVES_ONLY,
                RecursiveIteratorIterator::CATCH_GET_CHILD | RecursiveDirectoryIterator::SKIP_DOTS
            );

            iterator_apply(
                $iterator,
                function ($iterator) {
                    /** @var \FilesystemIterator $file */
                    $file = $iterator->current();
                    if (in_array($file->getExtension(), $this->imageExtensions)) {
                        foreach ($this->imageConverterService->getImageConverterValidator() as $imageConvertValidator) {
                            if ($imageConvertValidator->needsConversion($file->getPathname())) {
                                $this->eventManager->dispatch(
                                    'brocode_convert_image',
                                    [
                                        'image_path' => $file->getPathname(),
                                        'converter_id' => $imageConvertValidator->getConverterId()
                                    ]
                                );
                            }
                        }
                    }
                    return true;
                },
                [$iterator]
            );
        } catch (\Exception $e) {
            $this->logger->critical('BroCode - ImageOptimizer: ' . $e->getMessage());
        }
    }
}
