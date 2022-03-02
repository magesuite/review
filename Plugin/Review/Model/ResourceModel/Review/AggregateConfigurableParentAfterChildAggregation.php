<?php

namespace MageSuite\Review\Plugin\Review\Model\ResourceModel\Review;

class AggregateConfigurableParentAfterChildAggregation
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $configurableProductType;

    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableProductType,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->configurableProductType = $configurableProductType;
        $this->configuration = $configuration;
    }

    public function afterAggregate(\Magento\Review\Model\ResourceModel\Review $subject, $result, $object)
    {
        if (!$this->configuration->isAttachingToSimpleProductsEnabled()) {
            return $result;
        }

        $productId = $object->getEntityPkValue();
        $parentIds = $this->configurableProductType->getParentIdsByChild($productId);

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
