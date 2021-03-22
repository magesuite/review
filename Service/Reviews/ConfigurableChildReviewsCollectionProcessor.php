<?php

namespace MageSuite\Review\Service\Reviews;

class ConfigurableChildReviewsCollectionProcessor
{
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

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
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewsCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->reviewsCollectionFactory = $reviewsCollectionFactory;
    }

    public function getCollectionForConfigurableProduct($product)
    {
        if($this->reviewsCollection === null) {
            $productIds = $this->getProductIds($product);
            $this->createReviewCollection($productIds);
        }

        return $this->reviewsCollection;
    }

    protected function createReviewCollection($productIds)
    {
        $this->reviewsCollection = $this->reviewsCollectionFactory->create()
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder();

        $this->addProductFilter($productIds);

        return $this->reviewsCollection;
    }

    protected function addProductFilter($productIds)
    {
        $reviewEntityTable = $this->reviewsCollection->getTable('review_entity');
        $this->reviewsCollection->join($reviewEntityTable,'main_table.entity_id=' . $reviewEntityTable . '.entity_id', ['entity_code']);
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
}
