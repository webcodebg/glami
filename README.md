# Glami Pixel & Feed for Magento 2

GLAMI is a search engine that focuses on all styles of fashion, apparel and accessories which we neatly present and categorize. We are currently present in 14 different countries across Europe and South America.
GLAMI organises clothes, shoes and accessories from different merchants and presents them nicely altogether accessible for the end user to easily make a purchase based on their preferences.

This extension will allow you to generate the feed required to list your products on GLAMI as well as implement the pixel for better results.

## Install package
### Get Package

#### Magento 2.4.x (latest)
``` bash
composer require webcodebg/module-glami
```
### Setup after get package
``` bash
php bin/magento setup:upgrade
````
``` bash
php bin/magento setup:di:compile
php bin/magento setup:static-content-deploy
```
If your store supports different languages (excl. bg_BG) use
```` bash
php bin/magento setup:static-content-deploy en_US bg_BG
```` 

#### Flush cache
```` bash
php bin/magento cache:flush
````

## Module Configuration Settings
Stores - Configuration - Sales - Glami

## Generate Feed
Feed location is one of these:
``https://example.com/feed/glami-[store_code].xml``
or ``https://example.com/pub/feed/glami-[store_code].xml``
where ``[store_code]`` = code of the store. Default value: default.

Feed URL can be found here: Stores - Configuration - Sales - Glami - Feed

You can be generated via console command.
``` bash
php bin/magento glami:feed:generate
```
