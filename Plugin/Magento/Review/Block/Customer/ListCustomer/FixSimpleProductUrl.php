<?php

declare(strict_types=1);

namespace MageSuite\Review\Plugin\Magento\Review\Block\Customer\ListCustomer;

class FixSimpleProductUrl
{
    public function __construct(
        protected \MageSuite\Review\Model\ResourceModel\GetConfigurableProductUrl $getConfigurableProductUrl
    ) {
    }

    public function afterGetProductUrl(\Magento\Review\Block\Customer\ListCustomer $subject, $result, $product)
    {
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            return $result;
        }

        if ($parentUrl = $this->getConfigurableProductUrl->execute((int)$product->getId())) {
            return $parentUrl;
        }

        return $result;
    }
}
