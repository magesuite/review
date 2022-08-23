<?php

namespace MageSuite\Review\Service\Reviews;

class GroupedChildReviewsCollectionProcessor extends ChildReviewsCollectionProcessor
{
    public function getCollectionForGroupedProduct(\Magento\Catalog\Model\Product $product)
    {
        return $this->getCollection($product);
    }

    protected function getProductIds(\Magento\Catalog\Model\Product $product)
    {
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $associatedProductIds = $typeInstance->getAssociatedProductIds($product);

        return array_merge([$product->getId()], $associatedProductIds);
    }
}
