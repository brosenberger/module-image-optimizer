<?php

namespace BroCode\ImageOptimizer\Model;

use BroCode\ImageOptimizer\Api\Data\ImageConverterInterface;
use BroCode\ImageOptimizer\Api\Data\ImageConvertValidationInterface;
use Psr\Log\LoggerInterface;

class ImageConverterService
{

    /**
     * @var ImageConverterInterface[]
     */
    private $imageConverter;
    /**
     * @var ImageConvertValidationInterface[]
     */
    private $imageValidator;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param ImageConverterInterface[] $imageConverter
     * @param ImageConvertValidationInterface[] $imageValidator
     */
    public function __construct(
        LoggerInterface $logger,
        $imageConverter = [],
        $imageValidator = []
    ) {
        $this->logger = $logger;
        foreach ($imageConverter as $converter) {
            if (!$converter instanceof ImageConverterInterface) {
                $this->logger->critical('BroCode - ImageOptimizer: All converters must implement ImageConverterInterface, ' . get_class($converter) . ' does not!');
                continue;
            }
            $this->imageConverter[$converter->getConverterId()] = $converter;
        }
        foreach ($imageValidator as $validator) {
            if (!$validator instanceof ImageConvertValidationInterface) {
                $this->logger->critical('BroCode - ImageOptimizer: All validators must implement ImageConvertValidationInterface, ' . get_class($converter) . ' does not!');
                continue;
            }
            $this->imageValidator[$validator->getConverterId()] = $validator;
        }
    }

    public function getImageConverterValidator($converterId = null)
    {
        if ($converterId === null) {
            return $this->imageValidator;
        }
        if (isset($this->imageValidator[$converterId])) {
            return $this->imageValidator[$converterId];
        }
        return null;
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
