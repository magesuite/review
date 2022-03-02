<?php

namespace MageSuite\Review\Plugin\Review\Block\Product\ReviewRenderer;

class SummarizeReviewsInBuyBox
{
    /**
     * @var \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor
     */
    protected $reviewsCollectionProcessor;

    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $reviewsCollectionProcessor,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->reviewsCollectionProcessor = $reviewsCollectionProcessor;
        $this->configuration = $configuration;
    }

    public function afterGetReviewsCount(\Magento\Review\Block\Product\ReviewRenderer $subject, $result)
    {
        if (!$this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $result;
        }

        $product = $subject->getProduct();

        if ($product->getTypeId() !== \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $result;
        }

        $reviewCollection = $this->reviewsCollectionProcessor->getCollectionForConfigurableProduct($product);
        return $reviewCollection->getSize();
    }
}
