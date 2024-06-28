<?php

namespace BroCode\ImageOptimizer\Api\Data;

interface ImageConverterInterface
{
    /**
     * @return string
     */
    public function getConverterId();

    /**
     * @param $imagePath
     * @return void
     */
    public function convert($imagePath);
}
