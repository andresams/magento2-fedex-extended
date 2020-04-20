<?php
/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */
namespace Prestafy\FedexExtended\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

/**
 * Class Carrier
 * @package Prestafy\FedexExtended\Model
 */
class Carrier extends \Magento\Fedex\Model\Carrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $this->setRequest($request);
        $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     * Allows free shipping when all product items have free shipping (promotions etc.)
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return void
     */
    protected function _updateFreeMethodQuote($request)
    {
        $freeShipping = true;

        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getAddress()->getFreeShipping() !== true) {
                    $freeShipping = false;
                }
            }
        }

        $request->setFreeShipping($freeShipping);

        if ($freeShipping) {
            $freeRateId = false;
            $freeMethod = $this->getConfigData('free_method');
            if (is_object($this->_result)) {
                foreach ($this->_result->getAllRates() as $i => $item) {
                    if ($item->getMethod() == $freeMethod) {
                        $freeRateId = $i;
                        break;
                    }
                }
            }
            if ($freeMethod !== false) {
                $this->_result->getRateById($freeRateId)->setPrice(0);
            }
        }
    }
}
