<?php
namespace MageSuite\Review\Service\Reviews;

class ConfigurableChildReviewsCollectionProcessor
{
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewsCollectionFactory;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\Collection
     */
    protected $reviewsCollection;

    public function __construct(
        \Magento\Store\Model\StoreManager $storeManager,
        \MageSuite\Review\Helper\Configuration $configuration,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewsCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->reviewsCollectionFactory = $reviewsCollectionFactory;
    }

    public function getCollectionForConfigurableProduct(\Magento\Catalog\Model\Product $product)
    {
        if ($this->reviewsCollection === null) {
            $this->createReviewCollection($product);
        }

        return $this->reviewsCollection;
    }

    protected function createReviewCollection(\Magento\Catalog\Model\Product $product)
    {
        $productIds = $this->getProductIds($product);

        $this->reviewsCollection = $this->reviewsCollectionFactory->create()
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder();

        $this->addProductFilter($productIds);

        if ($this->configuration->isDisplayingVariantOnConfigurableReviewEnabled()) {
            $this->addReviewConfigurationDataToCollection($product);
        }

        return $this->reviewsCollection;
    }

    protected function addProductFilter($productIds)
    {
        $reviewEntityTable = $this->reviewsCollection->getTable('review_entity');
        $this->reviewsCollection->join($reviewEntityTable, 'main_table.entity_id=' . $reviewEntityTable . '.entity_id', ['entity_code']);
        $this->reviewsCollection->addFieldToFilter('entity_code', \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE);
        $this->reviewsCollection->addFieldToFilter('entity_pk_value', ['in' => $productIds]);
    }

    protected function getProductIds(\Magento\Catalog\Model\Product $product)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $usedProducts = $typeInstance->getUsedProducts($product);

        $productIds = [$product->getId()];
        foreach ($usedProducts as $usedProduct) {
            $productIds[] = $usedProduct->getId();
        }

        return $productIds;
    }

    protected function addReviewConfigurationDataToCollection(\Magento\Catalog\Model\Product $product)
    {
        foreach ($this->reviewsCollection as $review) {
            /** @var \Magento\Review\Model\Review $review */
            $review->setData('review_configuration_data', $this->getConfigurationData($product, $review->getEntityPkValue()));
        }
    }

    protected function getConfigurationData(\Magento\Catalog\Model\Product $configurableProduct, $simpleProductId)
    {
        $configurations = [];

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance = $configurableProduct->getTypeInstance();
        $usedProducts = $typeInstance->getUsedProducts($configurableProduct);

        $simpleProduct = array_filter($usedProducts, function ($usedProduct) use ($simpleProductId) {
            return (int)$usedProduct->getId() === (int)$simpleProductId;
        });

        if (empty($simpleProduct)) {
            return $configurations;
        }

        $simpleProduct = reset($simpleProduct);
        $configurableOptions = $typeInstance->getConfigurableOptions($configurableProduct);

        foreach ($configurableOptions as $attributeId => $attributeData) {
            foreach ($attributeData as $childAttribute) {
                if ($childAttribute['sku'] === $simpleProduct->getSku()) {
                    $configurations[$childAttribute['attribute_code']] = [
                        'label' => $childAttribute['super_attribute_label'],
                        'value' => $childAttribute['option_title']
                    ];
                }
            }
        }

        return $configurations;
    }
}
