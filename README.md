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
git 
Flush cache
```` bash
php bin/magento cache:flush
````

## Module Configuration Settings
Stores - Configuration - Sales - Glami
