<?php

namespace MageSuite\Review\Plugin\Review\Block\Product\ReviewRenderer;

class SummarizeReviewsInBuyBox
{
    protected \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $configurableReviewsCollectionProcessor;

    protected \MageSuite\Review\Service\Reviews\GroupedChildReviewsCollectionProcessor $groupedReviewsCollectionProcessor;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    public function __construct(
        \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $configurableReviewsCollectionProcessor,
        \MageSuite\Review\Service\Reviews\GroupedChildReviewsCollectionProcessor $groupedReviewsCollectionProcessor,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->configurableReviewsCollectionProcessor = $configurableReviewsCollectionProcessor;
        $this->groupedReviewsCollectionProcessor = $groupedReviewsCollectionProcessor;
        $this->configuration = $configuration;
    }

    public function afterGetReviewsCount(\Magento\Review\Block\Product\ReviewRenderer $subject, $result)
    {
        $product = $subject->getProduct();
        $productType = $product->getTypeId();

        if ($productType === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE && $this->configuration->isAttachingToSimpleProductsEnabled()) {
            $reviewCollection = $this->configurableReviewsCollectionProcessor->getCollectionForConfigurableProduct($product);
            return $reviewCollection->getSize();
        }

        if ($productType === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE && $this->configuration->isGroupedProductsShowReviewsFromAssignedProductsEnabled()) {
            $reviewCollection = $this->groupedReviewsCollectionProcessor->getCollectionForGroupedProduct($product);
            return $reviewCollection->getSize();
        }

        return $result;
    }
}
