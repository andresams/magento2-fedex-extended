/**
 * @category   Prestafy
 * @package    Prestafy_FedexExtended
 * @author     Andresa Martins <contact@andresa.dev>
 * @copyright  Copyright (c) 2020 Prestafy eCommerce Solutions (https://www.prestafy.com.br)
 * @license    https://prestafy.com.br/licenses/free-for-use PRESTAFY FREE FOR USE
 */

var config = {
    map: {
        '*': {
            'Magento_Checkout/template/cart/shipping-rates.html':
                'Prestafy_FedexExtended/template/cart/shipping-rates.html',
            'Magento_Checkout/template/shipping-address/shipping-method-item.html':
                'Prestafy_FedexExtended/template/shipping-address/shipping-method-item.html',
            'Magento_Tax/template/checkout/summary/shipping.html':
                'Prestafy_FedexExtended/template/checkout/summary/shipping.html',
            'Magento_Checkout/template/shipping-information.html':
                'Prestafy_FedexExtended/template/shipping-information.html'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/shipping': {
                'Prestafy_FedexExtended/js/view/summary/shipping-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Prestafy_FedexExtended/js/view/shipping-information-mixin': true
            }
        }
    }
};
