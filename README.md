# Magento 2 Glami Pixel

## Install package
``` bash
composer config repositories composer https://packagist.webcode.bg/
composer require webcode/magento2-glami
```

## Clean Cache
``` bash
php bin/magento setup:upgrade
````

##### In Develoment Mode
``` bash
php bin/magento cache:flush
```

##### In Production Mode
``` bash
php bin/magento setup:di:compile
php bin/magento setup:static-content-deploy
```
If your store supports different languages (excl. en_US) use
```` bash
php bin/magento setup:static-content-deploy en_US bg_BG [nl_NL]
```` 

Flush cache
```` bash
php bin/magento cache:flush
````