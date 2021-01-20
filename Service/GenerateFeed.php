<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 Webcode Ltd. (https://webcode.bg/)
 * @license      See LICENSE.txt for license details.
 */

namespace Webcode\Glami\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Simplexml\Element;
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
    const COLLECTION_LIMIT = 100;

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
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $file;

    /**
     * @var Configurable
     */
    private $configurable;

    private $store;

    private $progressBar;

    private $product;
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * Product Feed constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductStatus $productStatus
     * @param ProductRepository $productRepository
     * @param Configurable $configurable
     * @param StockRegistryInterface $stockRegistry
     * @param Helper $helper
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
        StockRegistryInterface $stockRegistry,
        Helper $helper,
        File $file
    ) {
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->stockRegistry = $stockRegistry;
        $this->helper = $helper;
        $this->file = $file;
    }

    /**
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar
     */
    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    /**
     * @return bool
     */
    public function hasProgressBar()
    {
        return $this->progressBar instanceof ProgressBar;
    }

    /**
     * @param null $storeCode
     *
     * @return array
     * @throws \Exception
     */
    public function execute($storeCode = null)
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
    protected function generateFeed(StoreInterface $store)
    {
        $xml = new Element("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><SHOP/>");

        $productsCollection = $this->getProductsCollection();

        foreach ($productsCollection as $product) {
            if ($this->stockRegistry->getStockStatusBySku($product->getSku())) {
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
        if (!is_dir($dir) || $this->file->fileExists($dir)) {
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
    protected function getProductsCollection($page = 0)
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
    private function addChildWithCData(\SimpleXMLElement $element, $name, $value)
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
    private function getParentProductId($childProductId)
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
    private function getProduct($parent = true)
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
}
