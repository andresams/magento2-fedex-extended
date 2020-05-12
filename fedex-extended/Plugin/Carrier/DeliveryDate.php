<?php
/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */
namespace Prestafy\FedexExtended\Plugin\Carrier;

use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;

/**
 * Class DeliveryDate
 * @package Prestafy\FedexExtended\Plugin\Carrier
 */
class DeliveryDate
{
    /**
     * @var ShippingMethodExtensionFactory
     */
    protected $extensionFactory;

    /**
     * DeliveryDate constructor.
     * @param ShippingMethodExtensionFactory $extensionFactory
     */
    public function __construct(
        ShippingMethodExtensionFactory $extensionFactory
    ) {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Add extension attribute delivery_date to Shipping method
     *
     * @param $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface $result
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface
     */
    public function afterModelToDataObject($subject, $result, $rateModel)
    {
        $extensionAttribute = $result->getExtensionAttributes() ? $result->getExtensionAttributes() :
            $this->extensionFactory->create();

        $extensionAttribute->setDeliveryDate($rateModel->getDeliveryDate());
        $result->setExtensionAttributes($extensionAttribute);

        return $result;
    }
}
