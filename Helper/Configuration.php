<?php

namespace MageSuite\Review\Helper;

class Configuration implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const REVIEW_CONFIGURABLE_PRODUCTS_CONFIG_PATH = 'review/configurable_products';

    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isAttachingToSimpleProductsEnabled()
    {
        return $this->getConfig()->getAllowAttachingReviewToSimpleProducts();
    }

    public function isDisplayingVariantOnConfigurableReviewEnabled()
    {
        return $this->getConfig()->getShowVariantOnConfigurableReview();
    }

    public function isReviewingSimpleProductsFromConfigurableViewEnabled()
    {
        return $this->getConfig()->getAllowReviewingSimpleProductsFromConfigurableView();
    }

    protected function getConfig()
    {
        if (!$this->config) {
            $this->config = new \Magento\Framework\DataObject(
                $this->scopeConfig->getValue(
                    self::REVIEW_CONFIGURABLE_PRODUCTS_CONFIG_PATH,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }

        return $this->config;
    }
}

