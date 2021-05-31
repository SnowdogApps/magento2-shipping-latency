# Snowdog Magento2 Shipping Latency

### 1. Installation:

* `composer require snowdog/module-shipping-latency`
* `bin/magento module:enable Snowdog_ShippingLatency`
* `bin/magento setup:upgrade`

### 2. Usage:

On product list/grid:
```php
$shippingLatencyHelper = $this->helper('Snowdog\ShippingLatency\Helper\Data');

//get shipping latency title per product
$shippingLatencyHelper->getTitle($productData);

//get full shipping latency popup data (title, btnClass, popupHtml, popupId) for all shipping_latency attribute values
$shippingLatencyHelper->getLatencyData();

//get full shipping latency popup data for product
$shippingLatencyHelper->getProductLatencyData($productData);

//get shipping latency popup CMS block content for product
$shippingLatencyHelper->getProductPopupHtml($productData);
```

On product page:
```php
//get product shipping_latency label
$product->getAttributeText('shipping_latency');

//get product shipping_latency value
$product->getShippingLatency();

//or use same method like on list/grid
```

```$productData``` needs to be an array. On product page use ```$product->getData()``` as parameter:

```php
$shippingLatencyHelper->getProductPopupHtml($product->getData());
```

Shipping latency options are configured on admin `Stores -> Configuration -> Snowdog -> Shipping Latency`.


Default options are settend as previous module version to mantain retrocompatibility.
