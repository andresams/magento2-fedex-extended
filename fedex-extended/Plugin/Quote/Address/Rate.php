<?php
/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */
namespace Prestafy\FedexExtended\Plugin\Quote\Address;
/**
 * Class Rate
 * @package Prestafy\FedexExtended\Plugin\Quote\Address
 */
class Rate
{
    /**
     * Add extension attribute delivery_date to Result model
     *
     * @param \Magento\Quote\Model\Quote\Address\AbstractResult $rate
     * @return \Magento\Quote\Model\Quote\Address\Rate
     */
    public function afterImportShippingRate($subject, $result, $rate)
    {
        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $result->setDeliveryDate(
                $rate->getDeliveryDate()
            );
        }

        return $result;
    }
}
