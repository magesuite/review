<?php

namespace MageSuite\Review\Plugin\MageSuite\Frontend\Helper\Review;

class AddReviewsSummaryFromRelatedSimpleProducts
{
    protected \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $voteCollectionFactory;

    protected \MageSuite\Frontend\Helper\ReviewFactory $reviewHelperFactory;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    public function __construct(
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $voteCollectionFactory,
        \MageSuite\Frontend\Helper\ReviewFactory $reviewHelperFactory,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->reviewHelperFactory = $reviewHelperFactory;
        $this->configuration = $configuration;
    }

    public function afterGetReviewSummary(\MageSuite\Frontend\Helper\Review $subject, array $reviewData, \Magento\Catalog\Model\Product $product, $includeVotes = false)
    {
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

        foreach ($childrenProducts as $childProduct) {
            $reviewHelper = $this->reviewHelperFactory->create([
                'voteCollection' => $this->voteCollectionFactory->create()
            ]);

            $childProductReviewData = $reviewHelper->getReviewSummary($childProduct, true);
            $reviewData = $this->summarizeReviewData($reviewData, $childProductReviewData);
        }

        $reviewData['data']['activeStars'] = $this->getStarsAmount($reviewData);

        return $reviewData;
    }

    protected function summarizeReviewData($baseData, $dataToAdd)
    {
        if ($dataToAdd['data']['count'] > 0) {
            foreach ($baseData['data']['votes'] as $key => $count) {
                $baseData['data']['votes'][$key] += $dataToAdd['data']['votes'][$key];
            }
        }

        return $baseData;
    }

    protected function getStarsAmount($reviewData)
    {
        if (!isset($reviewData['data']['votes'])) {
            return 0;
        }

        $count = 0;
        $value = 0;
        foreach ($reviewData['data']['votes'] as $rating => $num) {
            $count += $num;
            $value += $rating * $num;
        }

        if ($count == 0) {
            return 0;
        }

        return round($value / $count * 2) / 2;
    }
}
