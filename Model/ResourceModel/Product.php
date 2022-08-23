<?php

namespace MageSuite\Review\Model\ResourceModel;

class Product
{
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    protected \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable;

    protected \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
    }

    public function getProductType($productId)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            $connection->getTableName('catalog_product_entity'),
            ['type_id']
        )->where(
            'entity_id = ?',
            $productId
        );

        $product = $connection->fetchRow($select);

        if (empty($product)) {
            return null;
        }

        return $product['type_id'];
    }

    public function getChildrenIds($parentProductId, $productType)
    {
        if ($productType === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $this->configurable->getChildrenIds($parentProductId)[0];
        }

        if ($productType === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            return $this->grouped->getChildrenIds($parentProductId)[\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED];
        }

        return [];
    }
}
