<?php

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_multistore.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$reviewFactory = $objectManager->create(\Magento\Review\Model\ReviewFactory::class);
$ratingFactory = $objectManager->create(\Magento\Review\Model\RatingFactory::class);
$ratingResourceModel = $objectManager->create(\Magento\Review\Model\ResourceModel\Rating::class);
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$store = $objectManager->create(\Magento\Store\Model\Store::class);
$store->load('fixturestore', 'code');
$entityId = $ratingResourceModel->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE);
$productId = $productRepository->get('simple')->getId();

$reviewData = [
    ['store_id' => 1, 'rating' => 4],
    ['store_id' => 1, 'rating' => 4],
    ['store_id' => $store->getId(), 'rating' => 5],
    ['store_id' => $store->getId(), 'rating' => 5],
];

foreach ($reviewData as $data) {
    $review = $reviewFactory->create(['data' => ['nickname' => 'Nickname', 'title' => 'Review Summary', 'detail' => 'Review text']]);
    $review->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED);
    $review->setEntityId($entityId);
    $review->setEntityPkValue($productId);
    $review->setStores([$data['store_id']]);
    $review->save();

    $ratingFactory->create()
        ->setRatingId(1)
        ->setReviewId($review->getId())
        ->addOptionVote($data['rating'], 1);

    $review->aggregate();
}
