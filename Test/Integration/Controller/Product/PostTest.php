<?php

namespace MageSuite\Review\Test\Integration\Controller\Product;

class PostTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoConfigFixture current_store review/configurable_products/allow_attaching_review_to_simple_products 1
     */
    public function testItAllowsToAddReviewForANonVisibleSimpleOfConfigurable()
    {
        $postData = [
            'ratings' => [
                1 => '3',
            ],
            'nickname' => 'test',
            'title' => 'review title',
            'detail' => 'review description',
        ];

        $this->getRequest()
            ->setMethod('POST')
            ->setPostValue($postData);

        $this->dispatch('review/product/post/id/10');

        $this->assertSessionMessages(
            $this->equalTo(['You submitted your review for moderation.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
