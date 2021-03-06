<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Simplexml\Element;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Webcode\Glami\Helper\Data as Helper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class GenerateFeed
{
    public const COLLECTION_LIMIT = 100;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Webcode\Glami\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    private $productStatus;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var \Magento\InventorySalesApi\Api\AreProductsSalableInterface
     */
    private $areProductsSalable;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    /**
     * @var Configurable
     */
    private $configurable;

    private $store;

    private $filesystemDriver;

    private $progressBar;

    private $product;

    private $stockId;

    /**
     * Product Feed constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductStatus $productStatus
     * @param ProductRepository $productRepository
     * @param Configurable $configurable
     * @param \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver
     * @param \Magento\InventorySalesApi\Api\AreProductsSalableInterface $areProductsSalable
     * @param Helper $helper
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\DriverInterface $filesystemDriver
     * @param File $file
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        ProductStatus $productStatus,
        ProductRepository $productRepository,
        Configurable $configurable,
        StockResolverInterface $stockResolver,
        AreProductsSalableInterface $areProductsSalable,
        Helper $helper,
        DriverInterface $filesystemDriver,
        File $file
    ) {
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->stockResolver = $stockResolver;
        $this->areProductsSalable = $areProductsSalable;
        $this->helper = $helper;
        $this->filesystemDriver = $filesystemDriver;
        $this->file = $file;
    }

    /**
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar
     */
    public function setProgressBar(ProgressBar $progressBar): void
    {
        $this->progressBar = $progressBar;
    }

    /**
     * @return bool
     */
    public function hasProgressBar(): bool
    {
        return $this->progressBar instanceof ProgressBar;
    }

    /**
     * @param null $storeCode
     *
     * @return array
     * @throws \Exception
     */
    public function execute($storeCode = null): array
    {
        foreach ($this->storeManager->getStores() as $store) {
            /* @phpstan-ignore-next-line */
            if (($storeCode === null || $store->getCode() === $storeCode)
                && $store->getIsActive()
                && $this->helper->isActive($store->getId())
            ) {
                try {
                    $this->generateFeed($store);
                } catch (FileSystemException $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Genereate feed for every store.
     *
     * @param StoreInterface $store
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function generateFeed(StoreInterface $store): void
    {
        $xml = new Element("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><SHOP/>");

        $productsCollection = $this->getProductsCollection();
        if ($this->hasProgressBar()) {
            $this->progressBar->setMaxSteps($productsCollection->getSize());
        }

        foreach ($productsCollection as $product) {
            if ($this->isProductAvailable($store, $product->getSku())) {
                $this->product = $product;

                $item = $xml->addChild('SHOPITEM');
                $item->addChild('ITEM_ID', $product->getSku());
                $item->addChild('ITEMGROUP_ID', $this->getProduct()->getSku());
                $this->addChildWithCData($item, 'PRODUCTNAME', $product->getName());

                if ($description = $product->getDescription()) {
                    $this->addChildWithCData($item, 'DESCRIPTION', $description);
                }

                /* @phpstan-ignore-next-line */
                $item->addChild('URL', $this->getProduct()->getProductUrl());
                /* @phpstan-ignore-next-line */
                $item->addChild('URL_SIZE', $this->getProduct()->getProductUrl());

                $images = $product->getMediaGalleryImages();
                if ($images instanceof Collection) {
                    foreach ($images as $image) {
                        if ($product->getImage() !== $image->getFile()) {
                            $item->addChild('IMGURL_ALTERNATIVE', $image->getUrl());
                        } else {
                            $item->addChild('IMGURL', $image->getUrl());
                        }
                    }
                }

                /* @phpstan-ignore-next-line */
                $item->addChild('PRICE_VAT', $this->getProduct()->getFinalPrice());

                $item->addChild('CATEGORYTEXT', $product->getFinalPrice());

                if (($attributeCode = $this->helper->getAttributeCode('manufacturer')) &&
                    !empty($attributeCode) && $attributeValue = $product->getAttributeText($attributeCode)) {
                    $item->addChild('MANUFACTURER', $attributeValue);
                }

                if (($attributeCode = $this->helper->getAttributeCode('ean')) &&
                    !empty($attributeCode) && $attributeValue = $product->getAttributeText($attributeCode)) {
                    $item->addChild('EAN', $attributeValue);
                }

                if (($attributeCode = $this->helper->getAttributeCode('glami_cpc')) &&
                    !empty($attributeCode) && $attributeValue = $product->getAttributeText($attributeCode)) {
                    $item->addChild('GLAMI_CPC', $attributeValue);
                }

                if (($attributeCode = $this->helper->getAttributeCode('promotion_id')) &&
                    !empty($attributeCode) && $attributeValue = $product->getAttributeText($attributeCode)) {
                    $item->addChild('PROMOTION_ID', $attributeValue);
                }

                foreach ($this->helper->getAllowedAttributes() as $allowedAttribute) {
                    if (!empty($attributeValue) && !$attributeValue = $product->getAttributeText($allowedAttribute)) {
                        /* @phpstan-ignore-next-line */
                        $attributeValue = $this->getProduct()->getAttributeText($allowedAttribute);
                    }

                    if (!empty($attributeValue)) {
                        if (\is_array($attributeValue)) {
                            $attributeValue = implode(', ', $attributeValue);
                        }
                        $attribute = $item->addChild('PARAM');
                        $attribute->addChild('PARAM_NAME', $allowedAttribute);
                        $this->addChildWithCData($attribute, 'VALUE', $attributeValue);
                    }
                }

                /* @phpstan-ignore-next-line */
                if ($category = $this->helper->getGlamiCategory($this->getProduct()->getCategoryIds())) {
                    $item->addChild('CATEGORYTEXT', $category);
                }
            }

            if ($this->hasProgressBar()) {
                $this->progressBar->advance();
            }
        }

        $dir = $this->helper->getFeedPath();
        if (!$this->filesystemDriver->isDirectory($dir) || $this->file->fileExists($dir)) {
            $this->file->mkdir($dir, 0755);
        }

        $filename = $store->getCode() . '.xml';
        $xml->saveXML($dir . $filename);
    }

    /**
     * Get Products Collection
     *
     * @param int $page default 0
     *
     * @return object
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductsCollection($page = 0): object
    {
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*')->setStore($this->store);
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->addMediaGalleryData();
        $collection->addAttributeToFilter('is_saleable', 1);
        $collection->addFinalPrice();

        // Set limits if $page is greater than 0
        if ($page > 0) {
            $collection
                ->setPageSize(self::COLLECTION_LIMIT)
                ->setCurPage($page);
        }

        return $collection;
    }

    /**
     * @param \SimpleXMLElement $element
     * @param string $name
     * @param string|null $value
     */
    private function addChildWithCData(\SimpleXMLElement $element, string $name, ?string $value): void
    {
        $child = $element->addChild($name);
        $dom = dom_import_simplexml($child);
        $node = $dom->ownerDocument;
        $dom->appendChild($node->createCDATASection($value));
    }

    /**
     * @param int $childProductId
     *
     * @return int|bool
     */
    private function getParentProductId(int $childProductId)
    {
        $parentConfigObject = $this->configurable->getParentIdsByChild($childProductId);
        if ($parentConfigObject) {
            return $parentConfigObject[0];
        }

        return false;
    }

    /**
     * @param bool $parent
     *
     * @return Product|ProductInterface
     */
    private function getProduct($parent = true): ProductInterface
    {
        if ($parent && $parentProductId = $this->getParentProductId($this->product->getId())) {
            try {
                return $this->productRepository->getById($parentProductId);
            } catch (NoSuchEntityException $e) {
                $this->helper->logger($e->getMessage());

                return $this->product;
            }
        }

        return $this->product;
    }

    /**
     * @param StoreInterface $store
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStockIdByStore(StoreInterface $store): int
    {
        $storeId = $store->getId();
        if (!isset($this->stockId[$storeId])) {
            $websiteCode = $this->storeManager->getWebsite($store->getWebsiteId())->getCode();
            $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $this->stockId[$storeId] = (int)$stock->getStockId();
        }

        return (int)$this->stockId[$storeId];
    }

    /**
     * @param StoreInterface $store
     * @param string $sku
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isProductAvailable(StoreInterface $store, string $sku): bool
    {
        $stockId = $this->getStockIdByStore($store);
        $result = $this->areProductsSalable->execute([$sku], $stockId);
        if (\is_array($result)) {
            foreach ($result as $product) {
                if ($product->getSku() === $sku) {
                    return $product->isSalable();
                }
            }
        }

        return false;
    }
}
