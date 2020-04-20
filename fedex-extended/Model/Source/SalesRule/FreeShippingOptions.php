<?php
/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */
namespace Prestafy\FedexExtended\Model\Source\SalesRule;

use Magento\Framework\Data\OptionSourceInterface;
use Prestafy\FedexExtended\Model\SalesRule\Rule;

/**
 * Class FreeShippingOptions
 * @package Prestafy\FedexExtended\Model\Source\SalesRule
 */
class FreeShippingOptions implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 100.1.0
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('-- Select an option --')
            ],
            [
                'value' => Rule::FREE_SHIPPING_ORDER,
                'label' => __('Free Shipping for the entire order')
            ]
        ];
    }
}
