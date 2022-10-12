# Technical documentation

## Kernel file

The starting file for the Shop Plugin is [wc-getnet-redirect.php](/wc-getnet-redirect.php), with this file Wordpress system recognices that the folder is a plugin.

Within this file the plugins loads a [Kernel class](/app/Kernel.php) that contains the setups and hooks for the plugin.

### Relevant methods

On the /app/Kernel.php the relevant methods are:

| Method            | Description                                             |
| ----------------- | ------------------------------------------------------- |
| iniatilizeGateway | Initializes the gateway                                 |
| setup             | Manages the starting point when the plugin is installed |


## Payment generation

The class that takes care of generating the Payment URL is [app/GetNetRedirectGateway.php](/app/GetNetRedirectGateway.php), on the method `process_payment`.

## PostProcessing classes

The PostProcessing is done on the classes:

| Files                                                   | Description                                                                                  |
| ------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| app/GetNetRedirect/TransactionNextOperationCallback.php | Manages if the postprocess operation is valid and redirects the request to the correct class |
| app/GetNetRedirect/CancelledCallback.php                | Manages the cancelled postprocess operation                                                  |
| app/GetNetRedirect/RefundedCallback.php                 | Manages the refund postprocess operation                                                     |
| app/GetNetRedirect/PurchaseCallback.php                 | Manages the purchase operation                                                               |
| app/GetNetRedirect/CaptureAuthorizationCallback.php     | Manages the capture postprocess operation                                                    |

## Tests

The tests for the Shop Plugin are located on the [tests folder](/tests/README.md).

## Translations

To add a new translation to the module follow the [official Wordpress Guide](https://developer.wordpress.org/plugins/internationalization/localization/). 

Place the translations files on the /languages folder.