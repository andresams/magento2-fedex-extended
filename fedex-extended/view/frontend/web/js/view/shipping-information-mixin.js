/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */

define(['jquery', 'Magento_Checkout/js/model/quote'], function ($, quote) {
    'use strict';

    var mixin = {
        getShippingMethodDeliveryDate: function () {
            var shippingMethod = '',
                shippingMethodDeliveryDate = '';

            shippingMethod = quote.shippingMethod();

            if (!shippingMethod) {
                return '';
            }

            if (typeof shippingMethod['method_title'] !== 'undefined') {
                shippingMethodDeliveryDate = ' - ' + shippingMethod['method_title'];
                if (typeof shippingMethod['extension_attributes'] !== 'undefined') {
                    if (typeof shippingMethod['extension_attributes']['delivery_date'] !== 'undefined') {
                        shippingMethodDeliveryDate = shippingMethod['extension_attributes']['delivery_date'];
                    }
                }
            }

            return shippingMethodDeliveryDate;
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
