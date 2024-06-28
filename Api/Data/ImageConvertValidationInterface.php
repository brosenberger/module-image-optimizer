<?php

namespace BroCode\ImageOptimizer\Api\Data;

interface ImageConvertValidationInterface
{
    /**
     * @return string
     */
    public function getConverterId();

    /**
     * @param string $imagePath
     * @return boolean
     */
    public function needsConversion($imagePath);
}
