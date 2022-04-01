<?php
declare(strict_types=1);

namespace MageSuite\Review\Service;

class ReviewMultipleStoreAssigner
{
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    protected \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory;

    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\Review\Helper\Configuration $configuration,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(): void
    {
        foreach ($this->storeManager->getStores() as $store) {
            if (!$this->configuration->isShareReviewsBetweenStoresEnabled($store->getId())) {
                continue;
            }

            $this->process($store);
        }
    }

    protected function process(\Magento\Store\Api\Data\StoreInterface $store)
    {
        $additionalStoreIds = $this->configuration->getAdditionalStoresForShareReviewsBetweenStores($store);

        if (empty($additionalStoreIds)) {
            return;
        }

        $collection = $this->reviewCollectionFactory->create()
            ->addStoreData()
            ->addStoreFilter($store->getId())
            ->setPageSize(500);
        $lastPage = $collection->getLastPageNumber();
        $page = 1;
        $conditions = [];

        foreach ($additionalStoreIds as $additionalStoreId) {
            $conditions[] = $collection->getConnection()->quoteInto('store.store_id != ?', $additionalStoreId);
        }

        $collection->getSelect()
            ->where(implode(' OR ', $conditions));

        while ($page <= $lastPage) {
            $collection->setCurPage($page)->load();
            /** @var \Magento\Review\Model\Review $review */
            foreach ($collection as $review) {
                $this->addRatingToStore($review->getId(), $additionalStoreIds);
                $review->save();
                $review->aggregate();
            }

            $page++;
            $collection->clear();
        }
    }

    protected function addRatingToStore($reviewId, $storeIds): void
    {
        $ratingId = $this->getRatingIdByReview($reviewId);

        if (!$ratingId) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $data = [];

        foreach ($storeIds as $storeId) {
            $data[] = [
                'rating_id' => $ratingId,
                'store_id' => $storeId
            ];
        }

        $connection->insertOnDuplicate('rating_store', $data, ['rating_id', 'store_id']);
    }

    protected function getRatingIdByReview($reviewId): int
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('rating_option_vote'), ['rating_id'])
            ->where('review_id = ?', $reviewId);

        return (int)$connection->fetchOne($select);
    }
}
