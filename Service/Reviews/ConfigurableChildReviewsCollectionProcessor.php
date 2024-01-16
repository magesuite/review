<?php

namespace MageSuite\Review\Service\Reviews;

class ConfigurableChildReviewsCollectionProcessor extends ChildReviewsCollectionProcessor
{
    public function getCollectionForConfigurableProduct(\Magento\Catalog\Model\Product $product)
    {
        return $this->getCollection($product);
    }

    protected function getProductIds(\Magento\Catalog\Model\Product $product)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $childIds = $typeInstance->getChildrenIds($product->getId());

        return array_merge($childIds[0], [$product->getId()]);
    }

    public function addReviewConfigurationDataToItems(\Magento\Catalog\Api\Data\ProductInterface $product, array &$items): array
    {
        foreach ($items as $review) {
            /** @var \Magento\Review\Model\Review $review */
            $review->setData('review_configuration_data', $this->getConfigurationData($product, $review->getEntityPkValue()));
        }

        return $items;
    }

    protected function getConfigurationData(\Magento\Catalog\Model\Product $configurableProduct, $simpleProductId)
    {
        $configurations = [];
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance = $configurableProduct->getTypeInstance();
        $usedProducts = $typeInstance->getUsedProducts($configurableProduct);
        $simpleProduct = array_filter($usedProducts, function ($usedProduct) use ($simpleProductId) { //phpcs:ignore
            return (int)$usedProduct->getId() === (int)$simpleProductId;
        });

        if (empty($simpleProduct)) {
            return $configurations;
        }

        $simpleProduct = reset($simpleProduct);
        $configurableOptions = $typeInstance->getConfigurableOptions($configurableProduct);

        foreach ($configurableOptions as $attributeId => $attributeData) {
            foreach ($attributeData as $childAttribute) {
                if ($childAttribute['sku'] === $simpleProduct->getSku()) {
                    $configurations[$childAttribute['attribute_code']] = [
                        'label' => $childAttribute['super_attribute_label'],
                        'value' => $childAttribute['option_title']
                    ];
                }
            }
        }

        return $configurations;
    }
}
