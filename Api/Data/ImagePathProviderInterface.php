<?php

namespace BroCode\ImageOptimizer\Api\Data;

interface ImagePathProviderInterface
{
    /**
     * @return \Generator
     */
    public function getImagePaths() : \Generator;
}
