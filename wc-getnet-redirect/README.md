# WooCommerce Shop Plugin

## Requirements

WooCommerce v6.9.3

## Installation

Install the WooCommerce Shop Plugin zip file as other WooCommerce plugins. The common methods are:

- [Upload via WooCommerce Admin](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin)
- [Manual Plugin Installation](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation-1)

## Configuration

Once the plugin has been installed and enabled. The next step is to configure it.

### Enable the plugin

Navigate to the [WooCommerce Settings page](https://woocommerce.com/document/configuring-woocommerce-settings/). Select the Payments tab, it will show "GetNet" as a payment method.

![WooCommerce Settings page - Payments tab](/docs/images/woocommerce-payments-tab.jpg)

Click on "GetNet" method and configure the settings.

### Configurations

| Attribute     | Description           |
| ------------- |-------------|
| Enable gateway| Enables the gateway. Set as true|
| Enable testing mode| Enables the gateway testing mode. Set as false|
|User|Value provided by GetNet|
|Password|Value provided by GetNet|
|Merchant account resolver category|Value provided by GetNet|
|Merchant Secret Key|Value provided by GetNet|
|Merchant Secret Key for SEPA Credit|Value provided by GetNet|
|Creditor ID|Value provided by GetNet|

## Refund an order

To refund an order navigate to the GetNet Txs Page.

![Shop Plugin Menu](/docs/images/woocommerce-getnet-txs-log-menu.png)

It will list the last process orders. Select the order that you wish to refund by clicking on the _edit_ link.

![Shop Plugin Index](/docs/images/woocommerce-txs-index.png)

Select the operation to execute.

![Shop Plugin Capture or Cancel](/docs/images/woocommerce-capture-cancel.png)

## Cancel an order

To refund an order navigate to the GetNet Txs Page.

![Shop Plugin Menu](/docs/images/woocommerce-getnet-txs-log-menu.png)

It will list the last process orders. Select the order that you wish to cancel by clicking on the _edit_ link.

![Shop Plugin Index](/docs/images/woocommerce-txs-index.png)

Select the operation to execute.

![Shop Plugin Capture or Cancel](/docs/images/woocommerce-capture-cancel.png)

## Transactions log

To refund an order navigate to the GetNet Log Page.

![Shop Plugin Menu](/docs/images/woocommerce-getnet-txs-log-menu.png)

It will list all the transactions executed