<?php

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Review\Collection;

class AddAdditionalStoresToReviewCollection
{
    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\Review\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function beforeAddStoreFilter(\Magento\Review\Model\ResourceModel\Review\Collection $subject, $storeId)
    {
        if (!$this->configuration->isShareReviewsBetweenStoresEnabled()) {
            return [$storeId];
        }

        $additionalStores = $this->configuration->getAdditionalStoresForShareReviewsBetweenStores();
        if (empty($additionalStores)) {
            return [$storeId];
        }

        if (!is_array($storeId)) {
            $storeId = [$storeId];
        }

        $storeId = array_unique(array_merge($storeId, $additionalStores));

        return [$storeId];
    }
}