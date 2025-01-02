<?php

declare(strict_types=1);

namespace MageSuite\Review\Model\ResourceModel;

class GetConfigurableProductUrl
{
    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\UrlInterface $urlBuilder
    ) {
    }

    public function execute(int $productId): ?string
    {
        $connection = $this->resourceConnection->getConnection();

        $productRelationTable = $this->resourceConnection->getTableName('catalog_product_relation');
        $urlRewriteTable = $this->resourceConnection->getTableName('url_rewrite');

        $select = $connection->select()
            ->from(['cpr' => $productRelationTable], [])
            ->join(
                ['ur' => $urlRewriteTable],
                'cpr.parent_id = ur.entity_id AND ur.entity_type = "product"',
                ['request_path' => 'ur.request_path']
            )
            ->where('cpr.child_id = ?', $productId)
            ->where('ur.redirect_type = ?', 0)
            ->order('ur.store_id ASC')
            ->limit(1);

        $requestPath = $connection->fetchOne($select);

        if (!$requestPath) {
            return null;
        }

        return $this->urlBuilder->getUrl('', ['_direct' => $requestPath]);
    }
}
