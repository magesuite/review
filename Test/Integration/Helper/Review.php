<?php

namespace MageSuite\Review\Test\Integration\Helper;

class Review
{
    const QUALITY_RATING_ID = 1;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $ratingFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->storeManager = $storeManager;
    }

    public function createReview($productId, $rating, $status = \Magento\Review\Model\Review::STATUS_APPROVED)
    {
        $storeId = $this->storeManager->getStore()->getId();

        $this->ratingFactory->create()
            ->load(self::QUALITY_RATING_ID)
            ->setStores([$storeId])
            ->save();

        $review = $this->reviewFactory->create();
        $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($productId)
            ->setTitle('title')
            ->setDetail('description')
            ->setNickname('anonymous')
            ->setStatusId($status)
            ->setStores([$storeId])
            ->save();

        $this->ratingFactory->create()
            ->setRatingId(self::QUALITY_RATING_ID)
            ->setReviewId($review->getId())
            ->setStores([$storeId])
            ->addOptionVote($rating, $productId);

        $review->aggregate();
    }
}
