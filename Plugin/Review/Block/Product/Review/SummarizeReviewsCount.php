<?php

namespace MageSuite\Review\Plugin\Review\Block\Product\Review;

class SummarizeReviewsCount
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor
     */
    protected $reviewsCollectionProcessor;

    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $reviewsCollectionProcessor,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->coreRegistry = $registry;
        $this->reviewsCollectionProcessor = $reviewsCollectionProcessor;
        $this->configuration = $configuration;
    }

    public function aroundGetCollectionSize(\Magento\Review\Block\Product\Review $subject, callable $proceed)
    {
        if (!$this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $proceed();
        }

        $product = $this->getCurrentProduct();
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $proceed();
        }

        $reviewCollection = $this->reviewsCollectionProcessor->getCollectionForConfigurableProduct($product);
        return $reviewCollection->getSize();
    }

    protected function getCurrentProduct()
    {
        return $this->coreRegistry->registry('product');
    }
}
