<?php

namespace MageSuite\Review\Plugin\Review\Block\Product\Review;

class SummarizeReviewsCount
{
    protected \Magento\Framework\Registry $coreRegistry;

    protected \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $configurableReviewsCollectionProcessor;

    protected \MageSuite\Review\Service\Reviews\GroupedChildReviewsCollectionProcessor $groupedReviewsCollectionProcessor;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $configurableReviewsCollectionProcessor,
        \MageSuite\Review\Service\Reviews\GroupedChildReviewsCollectionProcessor $groupedReviewsCollectionProcessor,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->coreRegistry = $registry;
        $this->configurableReviewsCollectionProcessor = $configurableReviewsCollectionProcessor;
        $this->groupedReviewsCollectionProcessor = $groupedReviewsCollectionProcessor;
        $this->configuration = $configuration;
    }

    public function aroundGetCollectionSize(\Magento\Review\Block\Product\Review $subject, \Closure $proceed)
    {
        $product = $this->getCurrentProduct();
        $productType = $product->getTypeId();

        if ($productType === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE && $this->configuration->isAttachingToSimpleProductsEnabled()) {
            $reviewCollection = $this->configurableReviewsCollectionProcessor->getCollectionForConfigurableProduct($product);
            return $reviewCollection->getSize();
        }

        if ($productType === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE && $this->configuration->isGroupedProductsShowReviewsFromAssignedProductsEnabled()) {
            $reviewCollection = $this->groupedReviewsCollectionProcessor->getCollectionForGroupedProduct($product);
            return $reviewCollection->getSize();
        }

        return $proceed();
    }

    protected function getCurrentProduct()
    {
        return $this->coreRegistry->registry('product');
    }
}
