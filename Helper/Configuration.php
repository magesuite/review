<?php
declare(strict_types=1);

namespace MageSuite\Review\Helper;

class Configuration
{
    const XML_PATH_REVIEW_CONFIGURABLE_PRODUCTS_ALLOW_ATTACHING_REVIEW_TO_SIMPLE_PRODUCTS = 'review/configurable_products/allow_attaching_review_to_simple_products';
    const XML_PATH_REVIEW_CONFIGURABLE_PRODUCTS_SHOW_VARIANT_ON_CONFIGURABLE_REVIEW = 'review/configurable_products/show_variant_on_configurable_review';
    const XML_PATH_REVIEW_CONFIGURABLE_PRODUCTS_ALLOW_REVIEWING_SIMPLE_PRODUCTS_FROM_CONFIGURABLE_VIEW = 'review/configurable_products/allow_reviewing_simple_products_from_configurable_view';
    const XML_PATH_REVIEW_GROUPED_PRODUCTS_SHOW_REVIEWS_FROM_ASSIGNED_PRODUCTS = 'review/grouped_products/show_reviews_from_assigned_products';
    const XML_PATH_REVIEW_SHARE_BETWEEN_STORES_IS_ENABLED = 'review/share_between_stores/is_enabled';
    const XML_PATH_REVIEW_SHARE_BETWEEN_STORES_ADDITIONAL_STORES = 'review/share_between_stores/additional_stores';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isAttachingToSimpleProductsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REVIEW_CONFIGURABLE_PRODUCTS_ALLOW_ATTACHING_REVIEW_TO_SIMPLE_PRODUCTS);
    }

    public function isDisplayingVariantOnConfigurableReviewEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REVIEW_CONFIGURABLE_PRODUCTS_SHOW_VARIANT_ON_CONFIGURABLE_REVIEW);
    }

    public function isReviewingSimpleProductsFromConfigurableViewEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REVIEW_CONFIGURABLE_PRODUCTS_ALLOW_REVIEWING_SIMPLE_PRODUCTS_FROM_CONFIGURABLE_VIEW);
    }

    public function isGroupedProductsShowReviewsFromAssignedProductsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REVIEW_GROUPED_PRODUCTS_SHOW_REVIEWS_FROM_ASSIGNED_PRODUCTS);
    }

    public function isShareReviewsBetweenStoresEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REVIEW_SHARE_BETWEEN_STORES_IS_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAdditionalStoresForShareReviewsBetweenStores($storeId = null): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_REVIEW_SHARE_BETWEEN_STORES_ADDITIONAL_STORES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($value)) {
            return [];
        }

        return explode(',', $value);
    }
}
