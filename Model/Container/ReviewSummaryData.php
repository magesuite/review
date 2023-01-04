<?php

declare(strict_types=1);

namespace MageSuite\Review\Model\Container;

class ReviewSummaryData extends \Magento\Framework\DataObject
{
    protected \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory $summaryCollectionFactory;
    protected \Magento\Review\Model\Review\SummaryFactory $summaryFactory;

    public function __construct(
        \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory $summaryCollectionFactory,
        \Magento\Review\Model\Review\SummaryFactory $summaryFactory,
        array $data = []
    ) {
        parent::__construct($data);

        $this->summaryCollectionFactory = $summaryCollectionFactory;
        $this->summaryFactory = $summaryFactory;
    }

    public function initSummaries(array $productIds, int $storeId): void
    {
        $collection = $this->summaryCollectionFactory->create();

        $collection->addStoreFilter($storeId);
        $collection->addEntityFilter($productIds);

        $items = $collection->getItems();
        $missingProductIds = array_diff($productIds, array_keys($items));

        if (!empty($missingProductIds)) {
            $missingEntities = $this->createMissingEntities($missingProductIds);
            $items += $missingEntities;
        }

        $this->addData([$storeId => $items]);
    }

    public function getSummary(int $productId, int $storeId): ?\Magento\Review\Model\Review\Summary
    {
        return $this->getData(sprintf('%d/%d', $storeId, $productId));
    }

    protected function createMissingEntities(array $missingProductIds): array
    {
        $result = [];

        foreach ($missingProductIds as $productId) {
            $result[$productId] = $this->summaryFactory->create();
        }

        return $result;
    }
}
