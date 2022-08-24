<?php

declare(strict_types=1);

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Review\Collection;

/**
 * moved functionality from Service/Reviews/ConfigurableChildReviewsCollectionProcessor.php due to issue with pagination
 * which has been removed during setting additional data
 */
class AddReviewConfigurationDataToItems
{
    protected \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $configurableChildReviewsCollectionProcessor;
    protected \MageSuite\Review\Helper\Configuration $configuration;
    protected \Magento\Framework\Registry $registry;

    public function __construct(
        \MageSuite\Review\Service\Reviews\ConfigurableChildReviewsCollectionProcessor $configurableChildReviewsCollectionProcessor,
        \MageSuite\Review\Helper\Configuration $configuration,
        \Magento\Framework\Registry $registry
    ) {
        $this->configurableChildReviewsCollectionProcessor = $configurableChildReviewsCollectionProcessor;
        $this->configuration = $configuration;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Review\Model\ResourceModel\Review\Collection $collection
     * @param \Magento\Review\Model\ResourceModel\Review\Collection $items
     * @return array
     */
    public function afterGetItems(
        \Magento\Review\Model\ResourceModel\Review\Collection $collection,
        $items
    ) {
        if ($this->configuration->isDisplayingVariantOnConfigurableReviewEnabled() === false) {
            return $items;
        }

        $product = $this->getProduct();

        if (empty($product)) {
            return $items;
        }

        return $this->configurableChildReviewsCollectionProcessor->addReviewConfigurationDataToItems($product, $items);
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function getProduct(): ?\Magento\Catalog\Api\Data\ProductInterface
    {
        return $this->registry->registry('product');
    }
}
