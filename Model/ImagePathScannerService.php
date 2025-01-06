<?php

namespace BroCode\ImageOptimizer\Model;

use BroCode\ImageOptimizer\Api\Data\ImageConvertValidationInterface;
use BroCode\ImageOptimizer\Api\Data\ImagePathProviderInterface;
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
     * @var ImagePathProviderInterface[]
     */
    private array $imagePathProviders;

    /**
     * @param string[] $imageExtensions
     * @param ImagePathProviderInterface[] $imagePathProviders
     */
    public function __construct(
        LoggerInterface $logger,
        Manager $eventManager,
        ImageConverterService $imageConverterService,
        array $imagePathProviders = [],
        $imageExtensions = []
    ) {
        $this->imageExtensions = $imageExtensions;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->imageConverterService = $imageConverterService;
        $this->imagePathProviders = $imagePathProviders;
    }

    public function iteratePaths($conversionEventPostfix = '')
    {
        foreach ($this->imagePathProviders as $imagePathProvider) {
            foreach ($imagePathProvider->getImagePaths() as $imagePath) {
                $this->scanPath($imagePath, $conversionEventPostfix);
            }
        }
    }

    public function scanPath($directory, $conversionEventPostfix = '')
    {
        try {
            $iterator = new \RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory),
                RecursiveIteratorIterator::LEAVES_ONLY,
                RecursiveIteratorIterator::CATCH_GET_CHILD | RecursiveDirectoryIterator::SKIP_DOTS
            );

            iterator_apply(
                $iterator,
                function ($iterator) use ($conversionEventPostfix) {
                    /** @var \FilesystemIterator $file */
                    $file = $iterator->current();
                    if (in_array($file->getExtension(), $this->imageExtensions)) {
                        foreach ($this->imageConverterService->getImageConverterValidator() as $imageConvertValidator) {
                            if ($imageConvertValidator->needsConversion($file->getPathname())) {
                                $this->eventManager->dispatch(
                                    'brocode_convert_image' . $conversionEventPostfix,
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
