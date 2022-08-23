<?php

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Rating;

class CollectRatingSummaryForConfigurableAndGrouped
{
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    protected \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable;

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    protected \MageSuite\Review\Model\ResourceModel\Product $productResourceModel;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\Review\Helper\Configuration $configuration,
        \MageSuite\Review\Model\ResourceModel\Product $productResourceModel
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configurable = $configurable;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->productResourceModel = $productResourceModel;
    }

    public function aroundGetEntitySummary(\Magento\Review\Model\ResourceModel\Rating $subject, \Closure $proceed, $object, $onlyForCurrentStore = true)
    {
        $productType = $this->productResourceModel->getProductType($object->getEntityPkValue());

        if ($productType === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE && $this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $this->getEntitySummary($object, $onlyForCurrentStore, $productType);
        }

        if ($productType === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE && $this->configuration->isGroupedProductsShowReviewsFromAssignedProductsEnabled()) {
            return $this->getEntitySummary($object, $onlyForCurrentStore, $productType);
        }

        return $proceed($object, $onlyForCurrentStore);
    }

    protected function getEntitySummary($object, $onlyForCurrentStore, $productType)
    {
        $data = $this->_getEntitySummaryData($object, $productType);

        if ($onlyForCurrentStore) {
            foreach ($data as $row) {
                if ($row['store_id'] == $this->storeManager->getStore()->getId()) {
                    $object->addData($row);
                }
            }
            return $object;
        }

        $stores = $this->storeManager->getStores();

        $result = [];
        foreach ($data as $row) {
            $clone = clone $object;
            $clone->addData($row);
            $result[$clone->getStoreId()] = $clone;
        }

        $usedStoresId = array_keys($result);
        foreach ($stores as $store) {
            if (!in_array($store->getId(), $usedStoresId)) {
                $clone = clone $object;
                $clone->setCount(0);
                $clone->setSum(0);
                $clone->setStoreId($store->getId());
                $result[$store->getId()] = $clone;
            }
        }
        return array_values($result);
    }

    protected function _getEntitySummaryData($object, $productType)
    {
        $connection = $this->resourceConnection->getConnection();

        $sumColumn = new \Zend_Db_Expr("SUM(rating_vote.{$connection->quoteIdentifier('percent')})");
        $countColumn = new \Zend_Db_Expr("COUNT(*)");

        $select = $connection->select()->from(
            ['rating_vote' => $connection->getTableName('rating_option_vote')],
            ['entity_pk_value' => 'rating_vote.entity_pk_value', 'sum' => $sumColumn, 'count' => $countColumn]
        )->join(
            ['review' => $connection->getTableName('review')],
            'rating_vote.review_id=review.review_id',
            []
        )->joinLeft(
            ['review_store' => $connection->getTableName('review_store')],
            'rating_vote.review_id=review_store.review_id',
            ['review_store.store_id']
        );
        if (!$this->storeManager->isSingleStoreMode()) {
            $select->join(
                ['rating_store' => $connection->getTableName('rating_store')],
                'rating_store.rating_id = rating_vote.rating_id AND rating_store.store_id = review_store.store_id',
                []
            );
        }
        $select->join(
            ['review_status' => $connection->getTableName('review_status')],
            'review.status_id = review_status.status_id',
            []
        )->where(
            'review_status.status_code = :status_code'
        )->group(
            'review_store.store_id'
        );
        $bind = [':status_code' => \Magento\Review\Model\ResourceModel\Rating::RATING_STATUS_APPROVED];

        $entityPkValue = array_merge(
            [$object->getEntityPkValue()],
            $this->productResourceModel->getChildrenIds($object->getEntityPkValue(), $productType)
        );

        $entityPkValue = implode(',', $entityPkValue);

        if ($entityPkValue) {
            $select->where('rating_vote.entity_pk_value IN(' . $entityPkValue . ')');
        }

        return $connection->fetchAll($select, $bind);
    }
}
