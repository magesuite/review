<?php

namespace MageSuite\Review\Plugin\Catalog\Model\Product;

class AllowAddingOfReviewToChildOfAConfigurable
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \MageSuite\Review\Helper\Configuration $configuration
    ) {
        $this->request = $request;
        $this->configuration = $configuration;
    }

    public function afterIsVisibleInCatalog(\Magento\Catalog\Model\Product $subject, $result)
    {
        if ($this->configuration->isAttachingToSimpleProductsEnabled() && $this->request->getFullActionName() == 'review_product_post') {
            return true;
        }

        return $result;
    }

    public function afterIsVisibleInSiteVisibility(\Magento\Catalog\Model\Product $subject, $result)
    {
        if ($this->configuration->isAttachingToSimpleProductsEnabled() && $this->request->getFullActionName() == 'review_product_post') {
            return true;
        }

        return $result;
    }
}
