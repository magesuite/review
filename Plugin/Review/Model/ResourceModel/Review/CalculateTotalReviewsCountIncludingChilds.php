<?php

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Review;

class CalculateTotalReviewsCountIncludingChilds
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\Review\Helper\Configuration $configuration
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->configurable = $configurable;
        $this->configuration = $configuration;
    }

    public function aroundGetTotalReviews(
        \Magento\Review\Model\ResourceModel\Review $subject,
        callable $proceed,
        $entityPkValue,
        $approvedOnly = false,
        $storeId = 0)
    {
        if(!$this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $proceed($entityPkValue, $approvedOnly, $storeId);
        }

        $productType = $this->getProductType($entityPkValue);

        if ($productType == 'configurable') {
            return $this->getTotalReviews($entityPkValue, $approvedOnly, $storeId);
        }

        return $proceed($entityPkValue, $approvedOnly, $storeId);
    }

    public function getProductType($productId)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            $connection->getTableName('catalog_product_entity'),
            ['type_id']
        )->where(
            'entity_id IN (?)',
            $productId
        );

        $product = $connection->fetchRow($select);

        if (empty($product)) {
            return null;
        }

        return $product['type_id'];
    }

    protected function getChilds($parentProductId)
    {
        return $this->configurable->getChildrenIds($parentProductId)[0];
    }

    protected function getTotalReviews($entityPkValue, $approvedOnly, $storeId)
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
            $this->getChilds($entityPkValue)
        );

        $select->where(
            "{$reviewTable}.entity_pk_value IN(" . implode(',', $entityPkValue) . ")"
        );

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
