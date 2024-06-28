<?php

namespace BroCode\ImageOptimizer\Model\Converter;

use BroCode\ImageOptimizer\Api\Data\ImageConverterInterface;
use BroCode\ImageOptimizer\Api\Data\ImageConvertValidationInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractImageConverter implements ImageConvertValidationInterface, ImageConverterInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function needsConversion($imagePath)
    {
        return $this->isEnabled() &&
            (!is_file(($newFile = ($imagePath . $this->getConversionImageExtension()))) ||
            // check if file to convert is newer (in case of a manually converted file is uploaded)
            (is_file($newFile) && filemtime($imagePath) > filemtime($newFile)))
            ? $this->getConverterId() : null;
    }

    /**
     * @inheritDoc
     */
    public function convert($imagePath)
    {
        $newFile = $imagePath . $this->getConversionImageExtension();

        $this->doConvert($imagePath, $newFile);

        // if something happened on convertion and the file is created but is empty, remove it
        if (@filesize($newFile) == 0) {
            @unlink($newFile);
        } else {
            // synchronise modification time of webp and original file
            // see https://stackoverflow.com/questions/4898534/php-copy-file-without-changing-the-last-modified-date
            touch($newFile, filemtime($imagePath));

            $this->logger->debug('BroCode - ImageOptimizer: Image ' . $imagePath . ' converted to ' . $this->getConverterId() . ' format');
        }
    }

    /**
     * @return boolean
     */
    abstract protected function isEnabled();

    /*
     * @return string
     */
    abstract protected function getConversionImageExtension();

    /**
     * @param string $imagePath
     * @param string $newFile
     * @return boolean
     */
    abstract protected function doConvert($imagePath, $newFile);
}
