<?php

namespace BroCode\ImageOptimizer\Cron;

use BroCode\ImageOptimizer\Api\Constants;
use BroCode\ImageOptimizer\Api\Data\ImagePathProviderInterface;
use BroCode\ImageOptimizer\Model\ImagePathScannerService;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ImageScannerConverterCron
{
    /**
     * @var ImagePathScannerService
     */
    private $imagePathScannerService;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ImagePathScannerService $imagePathScannerService
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ImagePathScannerService $imagePathScannerService,
    ) {
        $this->imagePathScannerService = $imagePathScannerService;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        if ($this->scopeConfig->getValue(Constants::CONFIG_GENERAL_CRONENABLED, 'store') != true) {
            return;
        }

        $this->imagePathScannerService->iteratePaths();
    }
}
