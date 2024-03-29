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

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory;
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
use SimpleXMLElement;
use Symfony\Component\Console\Helper\ProgressBar;
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
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var \Webcode\Glami\Helper\Data
     */
    private Helper $helper;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private StoreInterface $store;

    /**
     * @var ProgressBar|null
     */
    private ?ProgressBar $progressBar = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private ProductCollectionFactory $productCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    private ProductStatus $productStatus;

    /**
     * @var \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    private StockResolverInterface $stockResolver;

    /**
     * @var int[]
     */
    private array $stockId = [];

    /**
     * @var \Magento\InventorySalesApi\Api\AreProductsSalableInterface
     */
    private AreProductsSalableInterface $areProductsSalable;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private Product $product;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory
     */
    private ConfigurableFactory $configurableFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private Product\Visibility $productVisibility;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private File $file;

    /**
     * Product Feed constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Helper $helper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductStatus $productStatus
     * @param \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver
     * @param \Magento\InventorySalesApi\Api\AreProductsSalableInterface $areProductsSalable
     * @param \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory $configurableFactory
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Framework\Filesystem $filesystem
     * @param File $file
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Helper $helper,
        ProductCollectionFactory $productCollectionFactory,
        ProductStatus $productStatus,
        StockResolverInterface $stockResolver,
        AreProductsSalableInterface $areProductsSalable,
        ConfigurableFactory $configurableFactory,
        ProductRepositoryInterface $productRepository,
        Product\Visibility $productVisibility,
        Filesystem $filesystem,
        File $file
    ) {
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->productCollection = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->stockResolver = $stockResolver;
        $this->areProductsSalable = $areProductsSalable;
        $this->configurableFactory = $configurableFactory;
        $this->productRepository = $productRepository;
        $this->productVisibility = $productVisibility;
        $this->filesystem = $filesystem;
        $this->file = $file;
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
     * Execute Generate Feed Service.
     *
     * @param string|null $storeCode
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function execute(string $storeCode = null): array
    {
        foreach ($this->storeManager->getStores() as $store) {
            /* @phpstan-ignore-next-line */
            if (($storeCode === null || $store->getCode() === $storeCode)
                && $store->getIsActive()
                && $this->helper->isActive($store->getId())
            ) {
                try {
                    $this->store = $store;
                    if ($this->progressBar instanceof ProgressBar) {
                        $this->progressBar->start();
                        $this->progressBar->clear();
                        $this->progressBar
                            ->setMessage(__('Generating Feed for %1 store...', $store->getName())->render());
                    }
                    $this->generateFeed();
                    if ($this->progressBar instanceof ProgressBar) {
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
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function generateFeed(): void
    {
        $xml = new Element("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><SHOP/>");

        $productsCollection = $this->getProductsCollection();
        $productsCollection->addMediaGalleryData();
        $productsCollection->addFinalPrice();

        if ($this->progressBar instanceof ProgressBar) {
            $this->progressBar->setMaxSteps($productsCollection->getSize());
        }

        $categoryMapping = $this->helper->getConfigData('feed/categories_attribute_enabled');
        $categoryAttribute = $this->helper->getConfigData('feed/category_attribute');
        $defaultSizeSystem = $this->helper->getConfigData('feed/size_system');

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

                $url = $this->getProduct()->getProductUrl();
                if ($utmParams = $this->helper->getUtmTracking()) {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . $utmParams;
                }

                /* @phpstan-ignore-next-line */
                $this->addChildWithCData($item, 'URL', $url);
                /* @phpstan-ignore-next-line */
                $this->addChildWithCData($item, 'URL_SIZE', $url);

                $images = $product->getMediaGalleryImages();
                if ($images instanceof Collection) {
                    foreach ($images as $image) {
                        /** @var \Magento\Framework\DataObject $image */
                        if ($product->getImage() !== $image->getData('file')) {
                            $item->addChild('IMGURL_ALTERNATIVE', $image->getData('url'));
                        } else {
                            $item->addChild('IMGURL', $image->getData('url'));
                        }
                    }
                }

                /* @phpstan-ignore-next-line */
                $item->addChild('PRICE_VAT', (string) $this->getProduct()->getFinalPrice());

                if ($attributeValue = $this->getAttributeValue($product, 'manufacturer')) {
                    $this->addChildWithCData($item, 'MANUFACTURER', $attributeValue);
                }

                if ($attributeValue = $this->getAttributeValue($product, 'size')) {
                    $item->addChild('SIZE', $attributeValue);
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

                $sizeSystemExists = false;
                foreach ($this->helper->getAllowedAttributes() as $allowedAttribute) {
                    if (empty($allowedAttribute)) {
                        continue;
                    }

                    $attributeValue = $this->getAttributeValue($product, $allowedAttribute, true);
                    if (empty($attributeValue)) {
                        $attributeValue = $this->getAttributeValue($this->getProduct(), $allowedAttribute, true);
                    }

                    if (is_string($attributeValue)) {
                        $attributeValue = trim($attributeValue);
                    }

                    if (!empty($attributeValue)) {
                        $attribute = $item->addChild('PARAM');
                        $attribute->addChild('PARAM_NAME', $allowedAttribute);
                        $this->addChildWithCData($attribute, 'VALUE', $attributeValue);
                        if ($allowedAttribute == 'size_system') {
                            $sizeSystemExists = true;
                        }
                    }
                }

                if (!$sizeSystemExists && !empty($defaultSizeSystem)) {
                    $attribute = $item->addChild('PARAM');
                    $attribute->addChild('PARAM_NAME', 'size_system');
                    $this->addChildWithCData($attribute, 'VALUE', $defaultSizeSystem);
                }

                /* @phpstan-ignore-next-line */
                if (!$categoryMapping &&
                    $category = $this->helper->getGlamiCategory($this->getProduct()->getCategoryIds())) {
                    $item->addChild('CATEGORYTEXT', $category);
                } elseif ($categoryMapping && !empty($categoryAttribute)) {
                    $category = $this->getAttributeValue($this->getProduct(), $categoryAttribute, true);
                    $item->addChild('CATEGORYTEXT', $category);
                }
            }
            if ($this->progressBar instanceof ProgressBar) {
                $this->progressBar->advance();
            }
        }

        $dir = $this->helper->getFeedPath();
        $this->file->checkAndCreateFolder($dir, 0755);

        try {
            $pub = $this->filesystem->getDirectoryWrite(DirectoryList::PUB);
            $pub->writeFile($this->helper->getFeedPath(true), (string)$xml->asXML());
        } catch (Exception $e) {
            $this->helper->logger($e->getMessage());
        }
    }

    /**
     * Get Products Collection.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
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
    private function addChildWithCData(SimpleXMLElement $element, string $name, ?string $value): void
    {
        $child = $element->addChild($name);
        $dom = dom_import_simplexml($child);
        $node = $dom->ownerDocument;
        if ($node) {
            $dom->appendChild($node->createCDATASection((string) $value));
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
        try {
            $parentConfigObject = $this->configurableFactory->create()->getParentIdsByChild($childProductId);
            if ($parentConfigObject) {
                return (int)$parentConfigObject[0];
            }
        } catch (Exception $e) {
            return 0;
        }

        return 0;
    }

    /**
     * Get Visible Product.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function getProduct(): ProductInterface
    {
        if ($parentProductId = $this->getParentProductId((int) $this->product->getId())) {
            try {
                $parentProduct = $this->productRepository->getById($parentProductId, false, $this->store->getId());
                if (in_array($parentProduct->getStatus(), $this->productStatus->getVisibleStatusIds())
                    && in_array($parentProduct->getVisibility(), $this->productVisibility->getVisibleInSiteIds())) {
                    return $parentProduct;
                }
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

        return $this->stockId[$storeId] ?? 0;
    }

    /**
     * Check Product availability.
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
     * @param Product $product
     * @param string $code
     * @param bool $isAttributeCode
     * @return string|null
     */
    private function getAttributeValue($product, string $code, bool $isAttributeCode = false): ?string
    {
        if ($isAttributeCode) {
            $attributeCode = $code;
        } else {
            $attributeCode = $this->helper->getAttributeCode($code);
        }

        if (!empty($attributeCode)) {
            if (!$attributeValue = $product->getAttributeText($attributeCode)) {
                $attributeValue = $product->getData($attributeCode);
            }

            return is_array($attributeValue) ? implode(',', $attributeValue) : (string) $attributeValue;
        }

        return null;
    }
}
