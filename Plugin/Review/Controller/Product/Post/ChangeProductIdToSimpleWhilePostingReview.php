<?php
namespace MageSuite\Review\Plugin\Review\Controller\Product\Post;

class ChangeProductIdToSimpleWhilePostingReview
{
    /**
     * @var \MageSuite\Review\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \MageSuite\Review\Helper\Configuration $configuration,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->configuration = $configuration;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    public function beforeDispatch(
        \Magento\Review\Controller\Product\Post $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->configuration->isReviewingSimpleProductsFromConfigurableViewEnabled()) {
            return null;
        }

        $superAttribute = $request->getParam('super_attribute');
        if ($superAttribute === null) {
            return null;
        }

        $configurableProduct = $this->getProduct((int)$request->getParam('id'));
        if (!$configurableProduct instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            return null;
        }

        if ($configurableProduct->getTypeId() !== \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return null;
        }

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableTypeInstance */
        $configurableTypeInstance = $configurableProduct->getTypeInstance();

        $simpleProduct = $configurableTypeInstance->getProductByAttributes($superAttribute, $configurableProduct);

        if (!$simpleProduct instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            return null;
        }

        $params = $request->getParams();
        $params['id'] = $simpleProduct->getId();
        $request->setParams($params);
        return [$request];
    }

    protected function getProduct($productId)
    {
        if (!$productId) {
            return false;
        }
        try {
            $product = $this->productRepository->getById($productId);
            if (!in_array($this->storeManager->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
                return false;
            }
            if (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
                return false;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
        return $product;
    }
}
