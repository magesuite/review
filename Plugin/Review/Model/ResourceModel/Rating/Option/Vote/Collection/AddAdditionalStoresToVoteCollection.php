<?php

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Rating\Option\Vote\Collection;

class AddAdditionalStoresToVoteCollection
{
    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\Review\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function aroundSetStoreFilter(\Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection $subject, callable $proceed, $storeId)
    {
        if (!$this->configuration->isShareReviewsBetweenStoresEnabled()) {
            return $proceed($storeId);
        }

        $additionalStores = $this->configuration->getAdditionalStoresForShareReviewsBetweenStores();
        if (empty($additionalStores)) {
            return $proceed($storeId);
        }

        if (!is_array($storeId)) {
            $storeId = [$storeId];
        }

        $storeId = array_unique(array_merge($storeId, $additionalStores));

        $subject->getSelect()->join(
            ['rstore' => $subject->getTable('review_store')],
            $subject->getConnection()->quoteInto(
                'main_table.review_id=rstore.review_id AND rstore.store_id IN (?)',
                $storeId
            ),
            []
        );

        return $subject;
    }
}
