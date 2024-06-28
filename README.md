# Image Optimizer Base - a Magento 2 setup for image optimizations

This module should ease the way of adding new image formats to a Magento 2 shop without the need of adapting any templates or markups.

** Goals of this module: **
* Base scanner of folders for images which might need to be served in modern formats (e.g. /pub/media)
* Framework for adding various different converter to be extendable for any new future image formats


[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/brosenberger)

## Installation

```
composer require brocode/module-chartee
bin/magento module:enable BroCode_Chartee
bin/magento setup:upgrade
```

## Features

### Cronjob for folder scanning of images

Scans all configured image folders, can be disabled via configuration.

**Configuration** 

The cronjob can be disabled under ```Stores -> Configuration -> Services -> BroCodeI ImageOptimizer```, it is enabled per default.

**Image Paths**

The cron job recieves via dependency injection ``BroCode\ImageOptimizer\Api\Data\ImagePathProviderInterface`` which can provide any directory to be scanned. 

````xml
<type name="\BroCode\ImageOptimizer\Cron\ImageScannerConverterCron">
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