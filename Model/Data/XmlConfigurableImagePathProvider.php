<?php

namespace BroCode\ImageOptimizer\Model\Data;

/**
 * Configurable image path provider, always based on the the Magento base path
 */
class XmlConfigurableImagePathProvider
{
    /**
     * @var array
     */
    private array $paths;

    public function __construct(
        $paths = []
    ) {
        $this->paths = $paths;
    }

    public function getImagePaths()
    {
        foreach ($this->paths as $path) {
            yield BP . DIRECTORY_SEPARATOR . $path;
        }
    }
}
