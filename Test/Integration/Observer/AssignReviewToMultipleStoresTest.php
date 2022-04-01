<?php

namespace MageSuite\Review\Test\Integration\Observer;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class AssignReviewToMultipleStoresTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Model\Store|mixed
     */
    protected $storeModel;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->storeModel = $objectManager->create(\Magento\Store\Model\Store::class);
        $this->productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->reviewCollectionFactory = $objectManager->create(\Magento\Review\Model\ResourceModel\Review\CollectionFactory::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store review/share_between_stores/is_enabled 0
     * @magentoDataFixture MageSuite_Review::Test/Integration/_files/reviews_multistore.php
     */
    public function testItAdditionalStoreIsNotIncluded(): void
    {
        $this->assertProductReviews(2);
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture fixturestore_store review/share_between_stores/is_enabled 1
     * @magentoConfigFixture fixturestore_store review/share_between_stores/additional_stores 1
     * @magentoDataFixture MageSuite_Review::Test/Integration/_files/reviews_multistore.php
     */
    public function testItAdditionalStoreIsIncluded(): void
    {
        $this->assertProductReviews(4);
    }

    protected function assertProductReviews(int $expectedCount): void
    {
        $productId = $this->productRepository->get('simple')->getId();
        $storeId = $this->storeModel->load('default')->getId();
        $collection = $this->reviewCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $productId);

        $this->assertEquals($expectedCount, $collection->getSize());
    }
}
