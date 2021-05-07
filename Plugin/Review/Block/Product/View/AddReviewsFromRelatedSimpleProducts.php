<?php

namespace MageSuite\Review\Plugin\Review\Block\Product\View;

class AddReviewsFromRelatedSimpleProducts
{
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor
     */
    protected $reviewsCollectionProcessor;

    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Store\Model\StoreManager $storeManager,
        \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $reviewsCollectionProcessor,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->storeManager = $storeManager;
        $this->reviewsCollectionProcessor = $reviewsCollectionProcessor;
        $this->configuration = $configuration;
    }

    public function aroundGetReviewsCollection(
        \Magento\Review\Block\Product\View $subject,
        callable $proceed
    ) {
        if(!$this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $proceed();
        }

        $product = $subject->getProduct();

        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $proceed();
        }

        return $this->reviewsCollectionProcessor->getCollectionForConfigurableProduct($product);
    }
}
