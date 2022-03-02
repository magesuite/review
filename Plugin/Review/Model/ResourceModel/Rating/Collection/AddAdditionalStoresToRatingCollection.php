<?php

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Rating\Collection;

class AddAdditionalStoresToRatingCollection
{
    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\Review\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function beforeSetStoreFilter(\Magento\Review\Model\ResourceModel\Rating\Collection $subject, $storeId)
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
