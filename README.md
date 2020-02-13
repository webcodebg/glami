# Magento 2 Glami Pixel

## Install package
``` bash
composer config repositories composer https://packagist.webcode.bg/
composer require webcode/magento2-glami
```

## Clean Cache
``` bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```