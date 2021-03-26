<?php
namespace MageSuite\Review\Test\Integration\Helper\Review;

class AddReviewsSummaryFromRelatedSimpleProductsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Review\Test\Integration\Helper\Review
     */
    protected $testReviewHelper;

    /**
     * @var \MageSuite\Frontend\Helper\Review
     */
    protected $frontendReviewHelper;

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
        $this->testReviewHelper = $this->objectManager->create(\MageSuite\Review\Test\Integration\Helper\Review::class);
        $this->frontendReviewHelper = $this->objectManager->create(\MageSuite\Frontend\Helper\Review::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->reviewModel = $this->objectManager->create(\Magento\Review\Model\Review::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoConfigFixture current_store review/configurable_products/allow_attaching_review_to_simple_products 1
     */
    public function testReviewSummaryIsCalculatedForApprovedReviewsIncludingAllChildProducts()
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
}
