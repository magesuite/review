<?php
declare(strict_types=1);

namespace MageSuite\Review\Observer;

class AssignReviewToMultipleStores implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\Review\Helper\Configuration $configuration;

    public function __construct(\MageSuite\Review\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var \Magento\Review\Model\Review $review */
        $review = $observer->getObject();
        $stores = (array)$review->getStores();
        $storesWithoutAdmin = array_diff($stores, [\Magento\Store\Model\Store::DEFAULT_STORE_ID]);

        if (count($storesWithoutAdmin) !== 1) {
            return;
        }

        $storeId = reset($storesWithoutAdmin);
        $additionalStoreIds = $this->configuration->getAdditionalStoresForShareReviewsBetweenStores($storeId);

        if (!$this->configuration->isShareReviewsBetweenStoresEnabled($storeId) || empty($additionalStoreIds)) {
            return;
        }

        $storeIds = array_unique(array_merge($stores, $additionalStoreIds));
        $review->setStores($storeIds);
    }
}
