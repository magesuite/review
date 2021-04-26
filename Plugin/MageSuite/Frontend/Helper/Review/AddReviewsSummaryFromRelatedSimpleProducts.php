<?php

namespace MageSuite\Review\Plugin\MageSuite\Frontend\Helper\Review;

class AddReviewsSummaryFromRelatedSimpleProducts
{
    /**
     * @var \MageSuite\Frontend\Helper\ReviewFactory
     */
    protected $reviewHelperFactory;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory
     */
    protected $voteCollectionFactory;

    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\Frontend\Helper\ReviewFactory $reviewHelperFactory,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory $voteCollectionFactory,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->reviewHelperFactory = $reviewHelperFactory;
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->configuration = $configuration;
    }

    public function afterGetReviewSummary(
        \MageSuite\Frontend\Helper\Review $subject,
        array $reviewData,
        \Magento\Catalog\Model\Product $product,
        $includeVotes = false
    ) {
        if ($product->getTypeId() !== \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $reviewData;
        }

        if (!$this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $reviewData;
        }

        $usedProducts = $product->getTypeInstance()->getUsedProducts($product);

        foreach ($usedProducts as $usedProduct) {
            $reviewHelper = $this->reviewHelperFactory->create([
                'voteCollection' => $this->voteCollectionFactory->create()
            ]);

            $usedProductReviewData = $reviewHelper->getReviewSummary($usedProduct, true);
            $reviewData = $this->summarizeReviewData($reviewData, $usedProductReviewData);
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

        return round($value / $count * 2) / 2;
    }
}
