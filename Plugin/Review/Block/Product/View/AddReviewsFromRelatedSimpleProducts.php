<?php

namespace MageSuite\Review\Plugin\Review\Block\Product\View;

class AddReviewsFromRelatedSimpleProducts
{
    protected \Magento\Store\Model\StoreManager $storeManager;

    protected \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $configurableReviewsCollectionProcessor;

    protected \MageSuite\Review\Service\Reviews\GroupedChildReviewsCollectionProcessor $groupedReviewsCollectionProcessor;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    public function __construct(
        \Magento\Store\Model\StoreManager $storeManager,
        \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $configurableReviewsCollectionProcessor,
        \MageSuite\Review\Service\Reviews\GroupedChildReviewsCollectionProcessor $groupedReviewsCollectionProcessor,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->storeManager = $storeManager;
        $this->configurableReviewsCollectionProcessor = $configurableReviewsCollectionProcessor;
        $this->groupedReviewsCollectionProcessor = $groupedReviewsCollectionProcessor;
        $this->configuration = $configuration;
    }

    public function aroundGetReviewsCollection(\Magento\Review\Block\Product\View $subject, \Closure $proceed)
    {
        $product = $subject->getProduct();
        $productType = $product->getTypeId();

        if ($productType === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE && $this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $this->configurableReviewsCollectionProcessor->getCollectionForConfigurableProduct($product);
        }

        if ($productType === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE && $this->configuration->isGroupedProductsShowReviewsFromAssignedProductsEnabled()) {
            return $this->groupedReviewsCollectionProcessor->getCollectionForGroupedProduct($product);
        }

        return $proceed();
    }
}
