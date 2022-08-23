<?php

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Review;

class AggregateParentAfterChildAggregation
{
    protected \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProductType;

    protected \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProductType;

    protected \MageSuite\Review\Helper\Configuration $configuration;

    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProductType,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProductType,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->configurableProductType = $configurableProductType;
        $this->groupedProductType = $groupedProductType;
        $this->configuration = $configuration;
    }

    public function afterAggregate(\Magento\Review\Model\ResourceModel\Review $subject, $result, $object)
    {
        $configurableParentIds = [];
        $groupedParentIds = [];

        $productId = $object->getEntityPkValue();

        if ($this->configuration->isAttachingToSimpleProductsEnabled()) {
            $configurableParentIds = $this->configurableProductType->getParentIdsByChild($productId);
        }

        if ($this->configuration->isGroupedProductsShowReviewsFromAssignedProductsEnabled()) {
            $groupedParentIds = $this->groupedProductType->getParentIdsByChild($productId);
        }

        $parentIds = array_merge($configurableParentIds, $groupedParentIds);

        if (empty($parentIds)) {
            return $result;
        }

        foreach ($parentIds as $parentId) {
            $reviewClone = clone $object;
            $reviewClone->unsetData();
            $reviewClone->setEntityPkValue($parentId);
            $reviewClone->setEntityId($object->getEntityId());
            $reviewClone->aggregate();
        }

        return $result;
    }
}
