# Image Optimizer Base - a Magento 2 setup for image optimizations

This module should ease the way of adding new image formats to a Magento 2 shop without the need of adapting any templates or markups.

**Goals of this module:**
* Base scanner of folders for images which might need to be served in modern formats (e.g. /pub/media)
* Framework for adding various different converter to be extendable for any new future image formats


[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/brosenberger)

## Installation

```
composer require brocode/module-image-optimizer
bin/magento module:enable BroCode_ImageOptimizer
bin/magento setup:upgrade
```

## Idea on how to delivery optimized images in a Magento 2 shop (or any other system)

Magento 2 is slow when delivering anything where a PHP process is involved in comparison to a simple file transfer for any file directly servable via the web server. This can be utilized to separate the conversion of the optimized images and serving them. 

1) The conversion takes place within the Magento2 environment (or any other) to determine which files need conversion and to which file they should be converted to. 

2) The webserver utilizes internal rewrites and file checks which file needs to be served, based on the request of the user agent (browser Accept-Header). 

Following .htaccess snippet can be used to deliver WEBP images for certain directories if they do exists in addition to the original one:

```
 ############################################
 ## if client accepts webp, rewrite image urls to use webp version
AddType image/webp .webp
RewriteCond %{HTTP_ACCEPT} image/webp
RewriteCond %{REQUEST_FILENAME} (.*)\.(png|gif|jpe?g)$
RewriteCond %{REQUEST_FILENAME}\.webp -f
RewriteRule ^ %{REQUEST_FILENAME}\.webp [L,T=image/webp]
```

Background information for this .htaccess entry:
* add a new mime type for webp images
* check if the browser accepts webp images
* check if the requested file is a png, gif or jpeg file
* check if a webp file exists for the requested file
* rewrite the request to the webp file

The same can be done with any other image format (e.g. for AVIF use the mime type image/avif).


## Features

### Cronjob for folder scanning of images

Scans all configured image folders, can be disabled via configuration.

**Configuration** 

The cronjob can be disabled under ```Stores -> Configuration -> Services -> BroCodeI ImageOptimizer```, it is enabled per default.

**Image Paths**

The ImagePathScannerService recieves via dependency injection ``BroCode\ImageOptimizer\Api\Data\ImagePathProviderInterface`` which can provide any directory to be scanned. 

````xml
<type name="BroCode\ImageOptimizer\Model\ImagePathScannerService">
    <arguments>
        <argument name="imagePathProviders" xsi:type="array">
            <item name="xmlConfigurable" xsi:type="object">BroCode\ImageOptimizer\Model\Data\XmlConfigurableImagePathProvider</item>
        </argument>
    </arguments>
</type>
````

One default path provider is implemented, which takes arguments via di.xml. The current setting is for the pub/media folder, the Magento base folder is added automatically to every entry given:
```xml
<type name="BroCode\ImageOptimizer\Model\Data\XmlConfigurableImagePathProvider">
    <arguments>
        <argument name="paths" xsi:type="array">
            <item name="media" xsi:type="string">pub/media</item>
        </argument>
    </arguments>
</type>
```

### CLI commands

Images Scanning (same function as cronjob + listing possibility of images to be optimized):
```
bin/magento images:optimize:scan
``` 

**Options:**
* `-l | --list`: List all images that need to be optimized, file is stored in ```var/<<datetime>>_image_optimizer.log```

### Conversion Hooks

This module provides an event hook for every image that needs to be converted. This is implemented with an default Magento 2 event and can be utilized with an observer listening on the event ```brocode_convert_image```. The event has following data stored that can be used:

```php
$this->eventManager->dispatch(
    'brocode_convert_image',
    [
        'image_path' => $file->getPathname(),
        'converter_id' => $imageConvertValidator->getConverterId()
    ]
);
```

**Convert Validator**

A convert validator checks if a given found image in any configured path needs conversion and which converter might be used for it. Every validator must implement ```BroCode\ImageOptimizer\Api\Data\ImageConvertValidationInterface```. A base implementation for file checks is implemented in the abstract class ```BroCode\ImageOptimizer\Model\Converter\AbstractImageConverter```.

These converter validator need to be contributed via di.xml to the ```\BroCode\ImageOptimizer\Model\ImageConverterService```:

```xml
<type name="BroCode\ImageOptimizer\Model\ImageConverterService">
    <arguments>
        ...
        <argument name="imageValidator" xsi:type="array">
            <item name="avif" xsi:type="object">BroCode\ImageAvifOptimizer\Model\Converter\AvifImageConverter</item>
        </argument>
    </arguments>
</type>
```

### Image conversion

There is currently no image conversion implemented in this module, this is done with following two basic modules:

* **brocode/module-image-optimizer-avif** (for AVIF generation)
* **brocode/module-image-optimizer-webp** (for WEBP generation)

Though there is the default ```BroCode\ImageOptimizer\Observer\InstantConvertImageObserver``` which catches the conversion event and try to convert the image with the help of any converter contributed to the ```\BroCode\ImageOptimizer\Model\ImageConverterService```:


```xml
<type name="BroCode\ImageOptimizer\Model\ImageConverterService">
    <arguments>
        <argument name="imageConverter" xsi:type="array">
            <item name="avif" xsi:type="object">BroCode\ImageAvifOptimizer\Model\Converter\AvifImageConverter</item>
        </argument>
        ...
    </arguments>
</type>
```

**This is done synchronously and slows down the according cron execution, especially if there are many images.**

Consider using the extensions for the usage of the Magento 2 queue system to asynchronously process image conversion:
* **brocode/module-image-optimizer-queue** (default MySQL queue for shops without active RabbitMQ installations)
* **brocode/module-image-optimizer-amqp** (extension to the queue module for configurations of the RabbitMQ services)


## Change Log

**1.1.0**
- Moved image path provider to service instead of cron job
- added CLI command to scan/optimize images + listing of images to be optimized

**1.0.0** 
- Initial version