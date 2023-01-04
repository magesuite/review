<?php

declare(strict_types=1);

namespace MageSuite\Review\Plugin\Review\Model\Review;

class UsePreloadedSummary
{
    protected \MageSuite\Review\Model\Container\ReviewSummaryData $summaryContainer;

    public function __construct(\MageSuite\Review\Model\Container\ReviewSummaryData $summaryContainer)
    {
        $this->summaryContainer = $summaryContainer;
    }

    public function aroundGetEntitySummary(\Magento\Review\Model\Review $subject, callable $proceed, $product, $storeId)
    {
        $summaryData = $this->summaryContainer->getSummary((int)$product->getId(), (int)$storeId);

        if ($summaryData) {
            $this->setSummaryForProduct($summaryData, $product);

            return;
        }

        $proceed($product, $storeId);
    }

    protected function setSummaryForProduct(\Magento\Review\Model\Review\Summary $summaryData, $product): void
    {
        $summary = new \Magento\Framework\DataObject();
        $summary->setData($summaryData->getData());
        $product->setRatingSummary($summary);
    }
}
