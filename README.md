# Magento 2 Glami Pixel

## Install package
``` bash
composer config repositories.webcode-magento2-glami git https://git.webcode.bg/magento2/glami.git
composer require webcode/magento2-glami:dev-master
```

## Clean Cache
``` bash
php bin/magento cache:clean
php bin/magento indexer:reindex
php bin/magento cache:clean
php bin/magento cache:flush
```