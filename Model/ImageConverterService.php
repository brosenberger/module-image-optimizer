<?php

namespace BroCode\ImageOptimizer\Model;

use BroCode\ImageOptimizer\Api\Data\ImageConverterInterface;
use Psr\Log\LoggerInterface;

class ImageConverterService
{

    /**
     * @var ImageConverterInterface[]
     */
    private array $imageConverter;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param ImageConverterInterface[] $imageConverter
     */
    public function __construct(
        LoggerInterface $logger,
        $imageConverter = []
    ) {
        $this->logger = $logger;
        foreach ($imageConverter as $converter) {
            if (!$converter instanceof ImageConverterInterface) {
                $this->logger->critical('BroCode - ImageOptimizer: All converters must implement ImageConverterInterface, ' . get_class($converter) . ' does not!');
                continue;
            }
            $this->imageConverter[$converter->getConverterId()] = $converter;
        }
    }

    public function getImageConverterValidator($converterId = null)
    {
        return $this->getImageConverter($converterId);
    }

    public function getImageConverter($converterId = null)
    {
        if ($converterId === null) {
            return $this->imageConverter;
        }
        if (isset($this->imageConverter[$converterId])) {
            return $this->imageConverter[$converterId];
        }
        return null;
    }
}
