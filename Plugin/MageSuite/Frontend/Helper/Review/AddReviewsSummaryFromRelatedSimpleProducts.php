<?php

namespace MageSuite\Review\Plugin\MageSuite\Frontend\Helper\Review;

class AddReviewsSummaryFromRelatedSimpleProducts
{
    protected \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $voteCollectionFactory;
    protected \MageSuite\Frontend\Helper\ReviewFactory $reviewHelperFactory;
    protected \MageSuite\Review\Helper\Configuration $configuration;
    protected \MageSuite\Review\Model\Container\ReviewSummaryData $summaryContainer;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $voteCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\Frontend\Helper\ReviewFactory $reviewHelperFactory,
        \MageSuite\Review\Helper\Configuration $configuration,
        \MageSuite\Review\Model\Container\ReviewSummaryData $summaryContainer
    ) {
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->reviewHelperFactory = $reviewHelperFactory;
        $this->configuration = $configuration;
        $this->summaryContainer = $summaryContainer;
        $this->storeManager = $storeManager;
    }

    public function afterGetReviewSummary(
        \MageSuite\Frontend\Helper\Review $subject,
        array $reviewData,
        \Magento\Catalog\Model\Product $product
    ): array {
        $productType = $product->getTypeId();
        $childrenProducts = [];

        if ($productType === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE && $this->configuration->isAttachingToSimpleProductsEnabled()) {
            $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
        }

        if ($productType === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE && $this->configuration->isGroupedProductsShowReviewsFromAssignedProductsEnabled()) {
            $childrenProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        }

        if (empty($childrenProducts)) {
            return $reviewData;
        }

        $this->preloadSummaries($childrenProducts, $product);

        foreach ($childrenProducts as $childProduct) {
            $reviewHelper = $this->reviewHelperFactory->create([
                'voteCollection' => $this->voteCollectionFactory->create(),
            ]);

            $childProductReviewData = $reviewHelper->getReviewSummary($childProduct, true);
            $reviewData = $this->summarizeReviewData($reviewData, $childProductReviewData);
        }

        $reviewData['data']['activeStars'] = $this->getStarsAmount($reviewData);

        return $reviewData;
    }

    protected function preloadSummaries(array $childrenProducts, \Magento\Catalog\Model\Product $parentProduct): void
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $productIds = array_map(function ($product) {
            return (int)$product->getId();
        }, $childrenProducts);

        $productIds[] = $parentProduct->getId();

        $this->summaryContainer->initSummaries($productIds, $storeId);
    }

    protected function summarizeReviewData($baseData, $dataToAdd): array
    {
        if ($dataToAdd['data']['count'] > 0) {
            foreach ($baseData['data']['votes'] as $key => $count) {
                $baseData['data']['votes'][$key] += $dataToAdd['data']['votes'][$key];
            }
        }

        return $baseData;
    }

    protected function getStarsAmount(array $reviewData): float
    {
        if (!isset($reviewData['data']['votes'])) {
            return 0;
        }

        $count = array_sum($reviewData['data']['votes']);

        if ($count === 0) {
            return 0;
        }

        $value = 0;

        foreach ($reviewData['data']['votes'] as $rating => $num) {
            $value += $rating * $num;
        }

        return round($value / $count * 2) / 2;
    }
}
