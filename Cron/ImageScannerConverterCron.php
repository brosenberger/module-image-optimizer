<?php

namespace BroCode\ImageOptimizer\Cron;

use BroCode\ImageOptimizer\Api\Constants;
use BroCode\ImageOptimizer\Api\Data\ImagePathProviderInterface;
use BroCode\ImageOptimizer\Model\ImagePathScannerService;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ImageScannerConverterCron
{
    /**
     * @var ImagePathProviderInterface[]
     */
    private array $imagePathProviders;
    /**
     * @var ImagePathScannerService
     */
    private $imagePathScannerService;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ImagePathProviderInterface[] $imagePathProviders
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ImagePathScannerService $imagePathScannerService,
        array $imagePathProviders = []
    ) {
        $this->imagePathProviders = $imagePathProviders;
        $this->imagePathScannerService = $imagePathScannerService;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        if ($this->scopeConfig->getValue(Constants::CONFIG_GENERAL_CRONENABLED, 'store') != true) {
            return;
        }

        foreach ($this->imagePathProviders as $imagePathProvider) {
            foreach ($imagePathProvider->getImagePaths() as $imagePath) {
                $this->imagePathScannerService->scanPath($imagePath);
            }
        }
    }
}
