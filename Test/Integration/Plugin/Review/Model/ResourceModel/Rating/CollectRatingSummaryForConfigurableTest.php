<?php

namespace MageSuite\Review\Test\Integration\Plugin\Review\Model\ResourceModel\Rating;

class CollectRatingSummaryForConfigurableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Review\Test\Integration\Helper\Review
     */
    protected $reviewHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Review\Model\Review
     */
    protected $reviewModel;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->reviewHelper = $this->objectManager->create(\MageSuite\Review\Test\Integration\Helper\Review::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->reviewModel = $this->objectManager->create(\Magento\Review\Model\Review::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoConfigFixture default/review/configurable_products/allow_attaching_review_to_simple_products 1
     */
    public function testRatingSummaryIsCalculatedForApprovedReviewsIncludingAllChildProducts()
    {
        $this->reviewHelper->createReview(10, 3);
        $this->reviewHelper->createReview(10, 4);
        $this->reviewHelper->createReview(10, 5);
        $this->reviewHelper->createReview(20, 1);
        $this->reviewHelper->createReview(20, 1, \Magento\Review\Model\Review::STATUS_PENDING);
        $this->reviewHelper->createReview(20, 1, \Magento\Review\Model\Review::STATUS_NOT_APPROVED);

        $product = $this->productRepository->get('configurable');

        $this->reviewModel->getEntitySummary($product, 1);
        $ratingSummary = $product->getRatingSummary();

        $this->assertEquals(65, $ratingSummary->getRatingSummary());
        $this->assertEquals(4, $ratingSummary->getReviewsCount());
    }
}
