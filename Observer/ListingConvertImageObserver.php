<?php

namespace BroCode\ImageOptimizer\Observer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class ListingConvertImageObserver implements ObserverInterface
{
    private $listingFile = null;

    protected WriteInterface $varDirectory;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    public function execute(Observer $observer)
    {
        $this->varDirectory->writeFile($this->getCurrentListingFile(),
            implode(";", [
                    $observer->getData('converter_id'),
                    $observer->getData('image_path')]
            ) . PHP_EOL,
            'a+'
        );
    }

    protected function getCurrentListingFile()
    {
        if ($this->listingFile == null) {
            $this->listingFile = date('Y-m-d_H-m-s_') . 'image-optimizer-listing.csv';
        }
        return $this->listingFile;
    }
}
