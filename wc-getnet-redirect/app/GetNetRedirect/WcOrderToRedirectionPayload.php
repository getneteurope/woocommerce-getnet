<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;

class WcOrderToRedirectionPayload
{
    /**
     * Retrieves the order formatted as a redirection payload
     *
     * @param mixed $order
     * @param mixed $merchantAccountResolverCategory
     * @return array
     */
    public static function getPayload($order, $merchantAccountResolverCategory, $creditorId)
    {
        $result = [];
        $requestId = current_time('YmdHis') . $order->get_id();
        $userLocaleArr = explode('_', get_user_locale());
        $userLocale = Constants::DEFAULT_LANGUAGE;
        if (count($userLocaleArr) > 0) {
            $userLocale = $userLocaleArr[0];
        }
        $result['payment'] = [
            'merchant-account-resolver-category' => $merchantAccountResolverCategory,
            'request-id' => $requestId,
            'transaction-type' => 'auto-sale',
            'requested-amount' => [
                'value' => $order->get_total(),
                'currency' => $order->get_currency(),
            ],
            'three-d' => [
                'attempt-three-d' => "true",
                'version' => "2.2"
            ],
            'account-holder' => [
                'merchant-crm-id' => $order->get_customer_id(),
                'first-name' => $order->get_billing_first_name(),
                'last-name' => $order->get_billing_last_name(),
                'phone' => $order->get_billing_phone(),
                'mobile-phone' => null,
                'work-phone' => null,
                'email' => $order->get_billing_email(),
                'address' => [
                    'street1' => $order->get_billing_address_1(),
                    'street2' => $order->get_billing_address_2(),
                    'street3' => null,
                    'city' => $order->get_billing_city(),
                    'postal-code' => $order->get_billing_postcode(),
                    'country' => $order->get_billing_country(),
                ]
            ],
            'shipping' => [
                'shipping-method' => $order->get_shipping_method(),
                'email' => $order->get_billing_email(),
                'address' => [
                    'street1' => $order->get_shipping_address_1(),
                    'street2' => $order->get_shipping_address_2(),
                    'street3' => null,
                    'city' => $order->get_shipping_city(),
                    'postal-code' => $order->get_shipping_postcode(),
                    'country' => $order->get_shipping_country(),
                ]
            ],
            'shop' => [
                'system-name' => "wordpress-pagos",
                'system-version' => \WC_VERSION,
                'plugin-name' => "wc-getnet-redirect",
                'plugin-version' => "1.0.6",
                'integration-type' => "redirect"
            ],
            'mandate' => [
                'mandate-id' => $order->get_id()
            ],
            'order-number' => $requestId,
            'creditor-id' => $creditorId,
            // removed descriptor since it fails the requests
            'descriptor' => 'ORDER ' . $requestId,
            'locale' => $userLocale,
            'ip-address' => "127.0.0.1",
            'success-redirect-url' => site_url() . '/wc-api/getnet-redirect-callback?order_id=' . $order->get_id(),
            'fail-redirect-url' => site_url() . '/wc-api/getnet-redirect-callback?order_id=' . $order->get_id(),
            'cancel-redirect-url' => site_url() . '/wc-api/getnet-redirect-callback?order_id=' . $order->get_id(),
        ];

        $orderItems = [];
        foreach ($order->get_items() as $item) {
            $unitPrice = round($item->get_total() / $item->get_quantity(), 2);
            $itemInfo = [
                'amount' => [
                    'currency' => $order->get_currency(),
                    'value' => $unitPrice,
                ],
                'article-number' => $item->get_product_id(),
                'description' => null,
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'tax-amount' => [
                    'currency' => $order->get_currency(),
                    'value' => $item->get_subtotal_tax(),
                ],
                'tax-rate' => null,
            ];
            $orderItems[] = $itemInfo;
        }

        if ($order->get_shipping_total() > 0) {
            $orderItems[] = [
                'amount' => [
                    'currency' => $order->get_currency(),
                    'value' => $order->get_shipping_total()
                ],
                'article-number' => "SHIPPING",
                'description' => "SHIPPING",
                'name' => "SHIPPING",
                'quantity' => 1,
                'tax-amount' => [
                    'currency' => $order->get_currency(),
                    'value' => 0
                ],
                'tax-rate' => 0
            ];
        }

        $result['payment']['order-items']['order-item'] = $orderItems;

        // Remove order items from zloty request
        if ($order->get_currency() == Constants::ZLOTY_ISO_CODE) {
            unset($result['payment']['order-items']);
        }

        return $result;
    }
}
