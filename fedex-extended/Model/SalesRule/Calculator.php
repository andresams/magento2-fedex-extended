<?php
/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */
namespace Prestafy\FedexExtended\Model\SalesRule;

use Magento\SalesRule\Model\Validator;

/**
 * Class Calculator
 * @package Prestafy\FedexExtended\Model\SalesRule
 */
class Calculator extends Validator
{
    /**
     * Applies the promotion rules created containing Fedex options
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    public function processFreeShipping(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $address = $item->getAddress();
        $item->setFreeShipping(false);

        foreach ($this->_getRules($address) as $rule) {
            /* @var $rule \Magento\SalesRule\Model\Rule */
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }

            switch ($rule->getFedexFreeShipping()) {
                case Rule::FREE_SHIPPING_ORDER:
                    $address->setFreeShipping(true);
                    break;
            }
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $this;
    }
}
