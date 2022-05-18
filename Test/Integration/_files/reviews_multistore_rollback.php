<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$collectionFactory = $objectManager->get(\Magento\Review\Model\ResourceModel\Review\CollectionFactory::class);
$collection = $collectionFactory->create();
foreach ($collection->getItems() as $review) {
    $review->delete();
}

\Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_multistore_rollback.php');
