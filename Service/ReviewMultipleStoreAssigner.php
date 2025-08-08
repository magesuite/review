<?php

declare(strict_types=1);

namespace MageSuite\Review\Service;

class ReviewMultipleStoreAssigner
{
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory;
    protected \MageSuite\Review\Helper\Configuration $configuration;

    protected array $processedReviewIds = [];

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->configuration = $configuration;
    }

    public function execute(): void
    {
        $this->linkAllRatingsToAllStores();

        foreach ($this->storeManager->getStores() as $store) {
            if (!$this->configuration->isShareReviewsBetweenStoresEnabled($store->getId())) {
                continue;
            }

            $this->process($store);
        }
    }

    protected function linkAllRatingsToAllStores(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $ratingTable = $connection->getTableName('rating');
        $ratingStoreTable = $connection->getTableName('rating_store');
        $storeTable = $connection->getTableName('store');

        $ratingSelect = $connection->select()->from($ratingTable, ['rating_id']);
        $ratingIds = $connection->fetchCol($ratingSelect);

        $storeSelect = $connection->select()->from($storeTable, ['store_id']);
        $storeIds = $connection->fetchCol($storeSelect);

        if (empty($ratingIds) || empty($storeIds)) {
            return;
        }

        $data = [];
        foreach ($ratingIds as $ratingId) {
            foreach ($storeIds as $storeId) {
                $data[] = [
                    'rating_id' => $ratingId,
                    'store_id' => $storeId
                ];
            }
        }

        if (!empty($data)) {
            $connection->insertOnDuplicate($ratingStoreTable, $data, ['rating_id', 'store_id']);
        }
    }

    protected function process(\Magento\Store\Api\Data\StoreInterface $store): void
    {
        $additionalStoreIds = $this->configuration->getAdditionalStoresForShareReviewsBetweenStores($store);

        $currentStoreId = (int)$store->getId();
        $additionalStoreIds = array_filter($additionalStoreIds, function($storeId) use ($currentStoreId) {
            return (int)$storeId !== $currentStoreId;
        });

        if (empty($additionalStoreIds)) {
            return;
        }

        $collection = $this->reviewCollectionFactory->create()
            ->addStoreData()
            ->addStoreFilter($store->getId())
            ->setPageSize(500);

        if (!empty($this->processedReviewIds)) {
            $collection->addFieldToFilter('main_table.review_id', ['nin' => $this->processedReviewIds]);
        }

        $lastPage = $collection->getLastPageNumber();
        $page = 1;

        while ($page <= $lastPage) {
            $collection->setCurPage($page)->load();

            $reviewIds = [];
            /** @var \Magento\Review\Model\Review $review */
            foreach ($collection as $review) {
                $reviewId = $review->getId();
                $reviewIds[] = $reviewId;
                $this->processedReviewIds[] = $reviewId;
            }

            if (!empty($reviewIds)) {
                $this->addReviewsToStores($reviewIds, $additionalStoreIds);

                foreach ($collection as $review) {
                    $review->aggregate();
                }
            }

            $page++;
            $collection->clear();
        }
    }

    protected function addReviewsToStores(array $reviewIds, array $storeIds): void
    {
        $connection = $this->resourceConnection->getConnection();
        $reviewStoreTable = $connection->getTableName('review_store');

        $data = [];
        foreach ($reviewIds as $reviewId) {
            foreach ($storeIds as $storeId) {
                $data[] = [
                    'review_id' => $reviewId,
                    'store_id' => $storeId
                ];
            }
        }

        if (!empty($data)) {
            $connection->insertOnDuplicate($reviewStoreTable, $data, ['review_id', 'store_id']);
        }
    }
}
