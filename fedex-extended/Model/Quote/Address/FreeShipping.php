<?php
/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */
declare(strict_types=1);

namespace Prestafy\FedexExtended\Model\Quote\Address;

use Magento\Quote\Model\Quote\Address\FreeShippingInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\StoreManagerInterface;
use Prestafy\FedexExtended\Model\SalesRule\Calculator;

class FreeShipping implements FreeShippingInterface
{
    /**
     * @var Calculator
     */
    protected $calculator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Calculator $calculator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Calculator $calculator
    ) {
        $this->storeManager = $storeManager;
        $this->calculator = $calculator;
    }

    /**
     * {@inheritDoc}
     */
    public function isFreeShipping(\Magento\Quote\Model\Quote $quote, $items)
    {
        /** @var \Magento\Quote\Api\Data\CartItemInterface[] $items */
        if (!count($items)) {
            return false;
        }

        $result = false;
        $addressFreeShipping = true;
        $store = $this->storeManager->getStore($quote->getStoreId());
        $this->calculator->init(
            $store->getWebsiteId(),
            $quote->getCustomerGroupId(),
            $quote->getCouponCode()
        );
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFreeShipping(0);
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($items as $item) {
            if ($item->getNoDiscount()) {
                $addressFreeShipping = false;
                $item->setFreeShipping(false);
                continue;
            }

            if ($item->getParentItemId()) {
                continue;
            }

            $this->calculator->processFreeShipping($item);
            // at least one item matches to the rule and the rule mode is not a strict
            if ((bool)$item->getAddress()->getFreeShipping()) {
                $result = true;
                break;
            }

            $itemFreeShipping = (bool)$item->getFreeShipping();
            $addressFreeShipping = $addressFreeShipping && $itemFreeShipping;
            $result = $addressFreeShipping;
        }

        $shippingAddress->setFreeShipping((int)$result);
        $this->applyToItems($items, $result);
        return $result;
    }

    /**
     * Sets free shipping to all quote items and children
     *
     * @param array $items
     * @param bool $freeShipping
     */
    private function applyToItems(array $items, bool $freeShipping)
    {
        /** @var AbstractItem $item */
        foreach ($items as $item) {
            $item->getAddress()
                ->setFreeShipping((int)$freeShipping);
            $this->applyToChildren($item, $freeShipping);
        }
    }

    /**
     * Sets free shipping too all children of a given item
     *
     * @param AbstractItem $item
     * @param bool $isFreeShipping
     */
    protected function applyToChildren(\Magento\Quote\Model\Quote\Item\AbstractItem $item, $isFreeShipping)
    {
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                $this->calculator->processFreeShipping($child);
                if ($isFreeShipping) {
                    $child->setFreeShipping($isFreeShipping);
                }
            }
        }
    }
}
