<?php
/*
 * @package      Webcode_Glami
 *
 * @author       Webcode, Kostadin Bashev (bashev@webcode.bg)
 * @copyright    Copyright © 2021 GLAMI Inspigroup s.r.o.
 * @license      See LICENSE.txt for license details.
 */

declare(strict_types=1);

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
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Simplexml\Element;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Webcode\Glami\Helper\Data as Helper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.Complexcity)
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

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var array
     */
    private array $stockId = [];

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private StoreInterface $store;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private Filesystem $filesystem;

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
     * @param \Magento\Framework\Filesystem $filesystem
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
        Filesystem $filesystem,
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
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    /**
     * Check for ProgressBar.
     *
     * @return bool
     */
    public function hasProgressBar(): bool
    {
        return $this->progressBar instanceof ProgressBar;
    }

    /**
     * Set ProgressBar to Console.
     *
     * @param \Symfony\Component\Console\Helper\ProgressBar $progressBar
     */
    public function setProgressBar(ProgressBar $progressBar): void
    {
        $this->progressBar = $progressBar;
    }

    /**
     * Execute Generate Feed Service
     *
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
                    $this->store = $store;
                    if ($this->hasProgressBar()) {
                        $this->progressBar->start();
                        $this->progressBar->clear();
                        $this->progressBar
                            ->setMessage(__('Generating Feed for %1 store...', $store->getName())->render());
                    }
                    $this->generateFeed();
                    if ($this->hasProgressBar()) {
                        $this->progressBar->finish();
                    }
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
     * @return void
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function generateFeed(): void
    {
        $xml = new Element("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><SHOP/>");

        $productsCollection = $this->getProductsCollection();
        if ($this->hasProgressBar()) {
            $this->progressBar->setMaxSteps($productsCollection->getSize());
        }

        $productsCollection->addMediaGalleryData();
        $productsCollection->addFinalPrice();

        foreach ($productsCollection as $product) {
            /** @var Product $product */
            if ($this->isProductAvailable($product->getSku())) {
                $this->product = $product;

                $item = $xml->addChild('SHOPITEM');
                $this->addChildWithCData($item, 'ITEM_ID', $product->getSku());
                $this->addChildWithCData($item, 'ITEMGROUP_ID', $this->getProduct()->getSku());
                $this->addChildWithCData($item, 'PRODUCTNAME', $product->getName());

                if ($description = $product->getData('description')) {
                    $this->addChildWithCData($item, 'DESCRIPTION', $description);
                }

                /* @phpstan-ignore-next-line */
                $this->addChildWithCData($item, 'URL', $this->getProduct()->getProductUrl());
                /* @phpstan-ignore-next-line */
                $this->addChildWithCData($item, 'URL_SIZE', $this->getProduct()->getProductUrl());

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
                $item->addChild('PRICE_VAT', (string) $this->getProduct()->getFinalPrice());

                if ($attributeValue = $this->getAttributeValue($product, 'manufacturer')) {
                    $this->addChildWithCData($item, 'MANUFACTURER', $attributeValue);
                }

                if ($attributeValue = $this->getAttributeValue($product, 'ean')) {
                    $item->addChild('EAN', $attributeValue);
                }

                if ($attributeValue = $this->getAttributeValue($product, 'glami_cpc')) {
                    $item->addChild('GLAMI_CPC', $attributeValue);
                }

                if ($attributeValue = $this->getAttributeValue($product, 'promotion_id')) {
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
        $this->file->checkAndCreateFolder($dir, 0755);

        try {
            $media = $this->filesystem->getDirectoryWrite(DirectoryList::PUB);
            $media->writeFile($this->helper->getFeedPath(true), (string)$xml->asXML());
        } catch (\Exception $e) {
            $this->helper->logger($e->getMessage());
        }
    }

    /**
     * Get Products Collection
     *
     * @param int $page default 0
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsCollection(): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*')->setStore($this->store);
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->addAttributeToFilter('is_saleable', ['eq' => 1]);

        return $collection;
    }

    /**
     * Add Data to XML.
     *
     * @param \SimpleXMLElement $element
     * @param string $name
     * @param string|null $value
     */
    private function addChildWithCData(\SimpleXMLElement $element, string $name, ?string $value): void
    {
        $child = $element->addChild($name);
        $dom = dom_import_simplexml($child);
        if (!empty($dom)) {
            $node = $dom->ownerDocument;
            $dom->appendChild($node->createCDATASection($value));
        }
    }

    /**
     * Check product for parent products and return it.
     *
     * @param int $childProductId
     *
     * @return int
     */
    private function getParentProductId(int $childProductId): int
    {
        $parentConfigObject = $this->configurable->getParentIdsByChild($childProductId);
        if ($parentConfigObject) {
            return (int) $parentConfigObject[0];
        }

        return 0;
    }

    /**
     * Get Visible Product.
     *
     * @param bool $parent
     *
     * @return Product|ProductInterface
     */
    private function getProduct(bool $parent = true): ProductInterface
    {
        if ($parent && $parentProductId = $this->getParentProductId((int)$this->product->getId())) {
            try {
                return $this->productRepository->getById($parentProductId, null, $this->store->getId());
            } catch (NoSuchEntityException $e) {
                $this->helper->logger($e->getMessage());

                return $this->product;
            }
        }

        return $this->product;
    }

    /**
     * Get Stock for Current Store.
     *
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
     * Check Product availability
     *
     * @param string $sku
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isProductAvailable(string $sku): bool
    {
        $stockId = $this->getStockIdByStore($this->store);
        $result = $this->areProductsSalable->execute([$sku], $stockId);
        foreach ($result as $product) {
            if ($product->getSku() === $sku) {
                return $product->isSalable();
            }
        }

        return false;
    }

    /**
     * Get Attribute Value for product, based on attribute code.
     *
     * @param $product
     * @param $code
     *
     * @return string|null
     */
    private function getAttributeValue($product, $code): ?string
    {
        if (($attributeCode = $this->helper->getAttributeCode($code))
            && $attributeValue = $product->getAttributeText($attributeCode)) {
            return $attributeValue;
        }

        return null;
    }
}
