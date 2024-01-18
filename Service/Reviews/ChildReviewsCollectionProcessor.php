<?php

namespace MageSuite\Review\Service\Reviews;

abstract class ChildReviewsCollectionProcessor
{
    protected \Magento\Store\Model\StoreManager $storeManager;

    protected \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewsCollectionFactory;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    protected ?\Magento\Review\Model\ResourceModel\Review\Collection $reviewsCollection;

    public function __construct(
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewsCollectionFactory,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->storeManager = $storeManager;
        $this->reviewsCollectionFactory = $reviewsCollectionFactory;
        $this->configuration = $configuration;
        $this->reviewsCollection = null;
    }

    public function getCollection(\Magento\Catalog\Model\Product $product)
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

        return $this->reviewsCollection;
    }

    protected function addProductFilter($productIds)
    {
        $reviewEntityTable = $this->reviewsCollection->getTable('review_entity');
        $this->reviewsCollection->join($reviewEntityTable, 'main_table.entity_id=' . $reviewEntityTable . '.entity_id', ['entity_code']);
        $this->reviewsCollection->addFieldToFilter('entity_code', \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE);
        $this->reviewsCollection->addFieldToFilter('main_table.entity_pk_value', ['in' => $productIds]);
    }

    abstract protected function getProductIds(\Magento\Catalog\Model\Product $product);
}
