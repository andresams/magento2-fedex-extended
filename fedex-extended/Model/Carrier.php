<?php
/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */
namespace Prestafy\FedexExtended\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

/**
 * Class Carrier
 * @package Prestafy\FedexExtended\Model
 */
class Carrier extends \Magento\Fedex\Model\Carrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezone;

    /**
     * Carrier constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Dir\Reader $configReader
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param array $data
     * @param Json|null $serializer
     * @param ClientFactory|null $soapClientFactory]
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = [],
        Json $serializer = null,
        ClientFactory $soapClientFactory = null
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $storeManager,
            $configReader,
            $productCollectionFactory,
            $data,
            $serializer,
            $soapClientFactory
        );

        $this->_timezone = $timezone;
    }

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

    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @param string $purpose
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _formRateRequest($purpose)
    {
        $r = $this->_rawRequest;

        $ratesRequest = [
            'WebAuthenticationDetail' => [
                'UserCredential' => ['Key' => $r->getKey(), 'Password' => $r->getPassword()],
            ],
            'ClientDetail' => ['AccountNumber' => $r->getAccount(), 'MeterNumber' => $r->getMeterNumber()],
            'Version' => $this->getVersionInfo(),
            'RequestedShipment' => [
                'DropoffType' => $r->getDropoffType(),
                'ShipTimestamp' => $this->calculatePickupDate(),
                'PackagingType' => $r->getPackaging(),
                'Shipper' => [
                    'Address' => ['PostalCode' => $r->getOrigPostal(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'Recipient' => [
                    'Address' => [
                        'PostalCode' => $r->getDestPostal(),
                        'CountryCode' => $r->getDestCountry(),
                        'StateOrProvinceCode' => $r->getDestRegionCode(),
                        'Residential' => (bool)$this->getConfigData('residence_delivery'),
                    ],
                ],
                'ShippingChargesPayment' => [
                    'PaymentType' => 'SENDER',
                    'Payor' => ['AccountNumber' => $r->getAccount(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'CustomsClearanceDetail' => [
                    'CustomsValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                ],
                'RateRequestTypes' => 'LIST',
                'PackageCount' => '1',
                'PackageDetail' => 'INDIVIDUAL_PACKAGES',
                'RequestedPackageLineItems' => [
                    '0' => [
                        'Weight' => [
                            'Value' => (double)$r->getWeight(),
                            'Units' => $this->getConfigData('unit_of_measure'),
                        ],
                        'GroupPackageCount' => 1,
                    ],
                ],
            ],
        ];

        if ($r->getDestCity()) {
            $ratesRequest['RequestedShipment']['Recipient']['Address']['City'] = $r->getDestCity();
        }

        if ($purpose != self::RATE_REQUEST_GENERAL && $purpose == self::RATE_REQUEST_SMARTPOST) {
            $ratesRequest['RequestedShipment']['ServiceType'] = self::RATE_REQUEST_SMARTPOST;
            $ratesRequest['RequestedShipment']['SmartPostDetail'] = [
                'Indicia' => (double)$r->getWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                'HubId' => $this->getConfigData('smartpost_hubid'),
            ];
        }

        //Request Estimated Delivery Date
        $ratesRequest['ReturnTransitAndCommit'] = true;

        return $ratesRequest;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareRateResponse($response)
    {
        $costArr = [];
        $priceArr = [];
        $deliveryDateArr = [];
        $errorTitle = 'For some reason we can\'t retrieve tracking info right now.';

        if (is_object($response)) {
            if ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR') {
                if (is_array($response->Notifications)) {
                    $notification = array_pop($response->Notifications);
                    $errorTitle = (string)$notification->Message;
                } else {
                    $errorTitle = (string)$response->Notifications->Message;
                }
            } elseif (isset($response->RateReplyDetails)) {
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                if (is_array($response->RateReplyDetails)) {
                    foreach ($response->RateReplyDetails as $rate) {
                        $serviceName = (string)$rate->ServiceType;
                        if (in_array($serviceName, $allowedMethods)) {
                            $amount = $this->_getRateAmountOriginBased($rate);
                            $costArr[$serviceName] = $amount;
                            $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                            if (!empty($rate->DeliveryTimestamp)) {
                                $deliveryDateArr[$serviceName] = $this->formatEstimatedDeliveryDate($rate->DeliveryTimestamp);
                            }
                        }
                    }
                    asort($priceArr);
                } else {
                    $rate = $response->RateReplyDetails;
                    $serviceName = (string)$rate->ServiceType;
                    if (in_array($serviceName, $allowedMethods)) {
                        $amount = $this->_getRateAmountOriginBased($rate);
                        $costArr[$serviceName] = $amount;
                        $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                    }
                }
            }
        }

        $result = $this->_rateFactory->create();
        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorTitle);
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($this->getCode('method', $method));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $rate->setDeliveryDate($deliveryDateArr[$method]);
                $result->append($rate);
            }
        }

        return $result;
    }

    /**
     * Format Delivery Timestamp
     *
     * @param $deliveryTimeStamp
     * @return \Magento\Framework\Phrase
     */
    public function formatEstimatedDeliveryDate($deliveryTimeStamp)
    {
        return __('Get it %1', date('l, F d', strtotime($deliveryTimeStamp)));
    }

    /**
     * Calculate and return Shipment Pickup Date
     * Pickup date will always be set to the next business day
     *
     * @return string
     */
    private function calculatePickupDate()
    {
        $currentTimeStamp = $this->_timezone->date()->format('c');
        $currentTimeStamp = date('c', strtotime($currentTimeStamp . ' +1 Weekday'));

        return $currentTimeStamp;
    }
}
