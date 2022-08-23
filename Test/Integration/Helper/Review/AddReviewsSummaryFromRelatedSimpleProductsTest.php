<?php

namespace MageSuite\Review\Test\Integration\Helper\Review;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class AddReviewsSummaryFromRelatedSimpleProductsTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\Review\Test\Integration\Helper\Review $testReviewHelper;

    protected ?\MageSuite\Frontend\Helper\Review $frontendReviewHelper;

    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    protected ?\Magento\Review\Model\Review $reviewModel;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->testReviewHelper = $this->objectManager->create(\MageSuite\Review\Test\Integration\Helper\Review::class);
        $this->frontendReviewHelper = $this->objectManager->create(\MageSuite\Frontend\Helper\Review::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->reviewModel = $this->objectManager->create(\Magento\Review\Model\Review::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoConfigFixture default/review/configurable_products/allow_attaching_review_to_simple_products 1
     */
    public function testReviewSummaryIsCalculatedForApprovedReviewsIncludingAllChildProductsForConfigurableProduct()
    {
        $this->testReviewHelper->createReview(10, 3);
        $this->testReviewHelper->createReview(10, 4);
        $this->testReviewHelper->createReview(10, 5);
        $this->testReviewHelper->createReview(20, 1);
        $this->testReviewHelper->createReview(20, 1, \Magento\Review\Model\Review::STATUS_PENDING);
        $this->testReviewHelper->createReview(20, 1, \Magento\Review\Model\Review::STATUS_NOT_APPROVED);

        $product = $this->productRepository->get('configurable');

        $this->reviewModel->getEntitySummary($product, 1);

        $reviewSummary = $this->frontendReviewHelper->getReviewSummary($product);

        $this->assertEquals(4, $reviewSummary['data']['count']);
        $this->assertEquals(3.5, $reviewSummary['data']['activeStars']);
    }

    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoConfigFixture default/review/grouped_products/show_reviews_from_assigned_products 1
     */
    public function testReviewSummaryIsCalculatedForApprovedReviewsIncludingAllChildProductsForGroupedProduct()
    {
        $this->testReviewHelper->createReview(1, 3);
        $this->testReviewHelper->createReview(1, 4);
        $this->testReviewHelper->createReview(1, 5);
        $this->testReviewHelper->createReview(21, 1);
        $this->testReviewHelper->createReview(21, 1, \Magento\Review\Model\Review::STATUS_PENDING);
        $this->testReviewHelper->createReview(21, 1, \Magento\Review\Model\Review::STATUS_NOT_APPROVED);

        $product = $this->productRepository->get('grouped-product');

        $this->reviewModel->getEntitySummary($product, 1);

        $reviewSummary = $this->frontendReviewHelper->getReviewSummary($product);

        $this->assertEquals(4, $reviewSummary['data']['count']);
        $this->assertEquals(3.5, $reviewSummary['data']['activeStars']);
    }
}
