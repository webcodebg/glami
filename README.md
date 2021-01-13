# Magento 2 Glami Pixel

## Install package
``` bash
composer require webcodebg/module-glami
```

``` bash
php bin/magento setup:upgrade
````

``` bash
php bin/magento setup:di:compile
php bin/magento setup:static-content-deploy
```
If your store supports different languages (excl. en_US) use
```` bash
php bin/magento setup:static-content-deploy en_US bg_BG
```` 

Flush cache
```` bash
php bin/magento cache:flush
````

## Module Configuration Settings
Stores - Configuration - Sales - Glami

## Generate Feed

Feed location is one of these: 
``https://example.com/feed/glami/[store_code].xml``
or ``https://example.com/pub/feed/glami/[store_code].xml``
where ``[store_code]`` = code of the store. Default value: default.

Feed URL can be found here: Stores - Configuration - Sales - Glami - Feed

You can be generated via console command.
``` bash
php bin/magento glami:feed:generate
```
