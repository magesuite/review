<?php

namespace MageSuite\Review\Test\Integration\Plugin\Review\Model\ResourceModel\Review\Collection;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class AddAdditionalStoresToReviewCollectionTest extends \PHPUnit\Framework\TestCase
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

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->storeModel = $this->objectManager->create(\Magento\Store\Model\Store::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->reviewCollectionFactory = $this->objectManager->create(\Magento\Review\Model\ResourceModel\Review\CollectionFactory::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store review/share_between_stores/is_enabled 0
     * @magentoDataFixture MageSuite_Review::Test/Integration/_files/reviews_multistore.php
     */
    public function testItAdditionalStoreIsNotIncluded()
    {
        $expectedCount = 2;
        $productId = $this->productRepository->get('simple')->getId();
        $storeId = $this->storeModel->load('fixturestore')->getId();

        $collection = $this->reviewCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $productId);

        $this->assertEquals($expectedCount, $collection->getSize());
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store review/share_between_stores/is_enabled 1
     * @magentoConfigFixture current_store review/share_between_stores/additional_stores 1
     * @magentoDataFixture MageSuite_Review::Test/Integration/_files/reviews_multistore.php
     */
    public function testItAdditionalStoreIsIncluded()
    {
        $expectedCount = 4;
        $productId = $this->productRepository->get('simple')->getId();
        $storeId = $this->storeModel->load('fixturestore')->getId();

        $collection = $this->reviewCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $productId);

        $this->assertEquals($expectedCount, $collection->getSize());
    }
}
