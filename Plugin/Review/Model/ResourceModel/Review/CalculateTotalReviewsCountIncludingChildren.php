<?php

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Review;

class CalculateTotalReviewsCountIncludingChildren
{
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    protected \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable;

    protected \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    protected \MageSuite\Review\Model\ResourceModel\Product $productResourceModel;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \MageSuite\Review\Helper\Configuration $configuration,
        \MageSuite\Review\Model\ResourceModel\Product $productResourceModel
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->configuration = $configuration;
        $this->productResourceModel = $productResourceModel;
    }

    public function aroundGetTotalReviews(\Magento\Review\Model\ResourceModel\Review $subject, \Closure $proceed, $entityPkValue, $approvedOnly = false, $storeId = 0)
    {
        $productType = $this->productResourceModel->getProductType($entityPkValue);

        if ($productType === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE && $this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $this->getTotalReviews($entityPkValue, $approvedOnly, $storeId, $productType);
        }

        if ($productType === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE && $this->configuration->isGroupedProductsShowReviewsFromAssignedProductsEnabled()) {
            return $this->getTotalReviews($entityPkValue, $approvedOnly, $storeId, $productType);
        }

        return $proceed($entityPkValue, $approvedOnly, $storeId);
    }

    protected function getTotalReviews($entityPkValue, $approvedOnly, $storeId, $productType) //phpcs:ignore
    {
        $connection = $this->resourceConnection->getConnection();

        $reviewTable = $connection->getTableName('review');
        $reviewStoreTable = $connection->getTableName('review_store');

        $select = $connection->select()->from(
            $reviewTable,
            ['review_count' => new \Zend_Db_Expr('COUNT(*)')]
        );

        $entityPkValue = array_merge(
            [$entityPkValue],
            $this->productResourceModel->getChildrenIds($entityPkValue, $productType)
        );

        $select->where("{$reviewTable}.entity_pk_value IN(?)", $entityPkValue);

        if ($storeId > 0) {
            $select->join(
                ['store' => $reviewStoreTable],
                $reviewTable . '.review_id=store.review_id AND store.store_id = :store_id',
                []
            );
            $bind[':store_id'] = (int)$storeId;
        }

        if ($approvedOnly) {
            $select->where("{$reviewTable}.status_id = :status_id");
            $bind[':status_id'] = \Magento\Review\Model\Review::STATUS_APPROVED;
        }

        return $connection->fetchOne($select, $bind);
    }
}
