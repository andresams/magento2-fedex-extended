# FedEx Extended
This module extends Magento's native Fedex module with a lot of cool features, such as:

 - Free Shipping using Promotions (Cart Rules)
 - Display estimated delivery date
 - Use only weekdays as possible pickup dates (pickup date is moved to the next weekday when shipping rates are requested during an weekend)
 - ~~Add extra days to the pickup date~~

## Usage

The management options can be found under Stores > Stores > Configuration > Shipping Methods > FedEx.

## Installation

### Composer

```bash
composer config repositories.andresams git git@github.com:andresams/magento2-fedex-extended.git
composer require prestafy/magento2-fedex-extended
```

### Modman

```bash
modman clone git@github.com:andresams/magento2-fedex-extended.git
```

## Authors

* [Andresa Martins](https://www.andresa.dev) - contact@andresa.dev
