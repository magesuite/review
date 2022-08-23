<?php

namespace MageSuite\Review\Service\Reviews;

class ConfigurableChildReviewsCollectionProcessor extends ChildReviewsCollectionProcessor
{
    public function getCollectionForConfigurableProduct(\Magento\Catalog\Model\Product $product)
    {
        return $this->getCollection($product);
    }

    protected function createReviewCollection(\Magento\Catalog\Model\Product $product)
    {
        parent::createReviewCollection($product);

        if ($this->configuration->isDisplayingVariantOnConfigurableReviewEnabled()) {
            $this->addReviewConfigurationDataToCollection($product);
        }

        return $this->reviewsCollection;
    }

    protected function getProductIds(\Magento\Catalog\Model\Product $product)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $usedProducts = $typeInstance->getUsedProducts($product);

        $productIds = [$product->getId()];
        foreach ($usedProducts as $usedProduct) {
            $productIds[] = $usedProduct->getId();
        }

        return $productIds;
    }

    protected function addReviewConfigurationDataToCollection(\Magento\Catalog\Model\Product $product)
    {
        foreach ($this->reviewsCollection as $review) {
            /** @var \Magento\Review\Model\Review $review */
            $review->setData('review_configuration_data', $this->getConfigurationData($product, $review->getEntityPkValue()));
        }
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
