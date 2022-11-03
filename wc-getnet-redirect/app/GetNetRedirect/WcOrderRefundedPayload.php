<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;

class WcOrderRefundedPayload
{
    /**
     * Retrieve the payload for cancellation
     *
     * @return array
     */
    public static function getPayload(
        $order,
        $settings,
        $processedBy,
        $purchaseRequestId,
        $purchasePaymentMethod,
        $purchaseTransactionType,
        $merchantAccountId,
        $transactionId
    ) {
        $payload = [];
        $requestId = current_time('YmdHis') . $order->get_id();
        $transactionType = null;

        // Transaction Type Logic
        switch ($purchasePaymentMethod) {
            case Constants::PURCHASE_METHOD_ALIPAY_XBORDER:
                if ($purchaseTransactionType === Constants::TX_DEBIT) {
                    $transactionType = Constants::TX_REFUND_DEBIT;
                }
                break;
            case Constants::PURCHASE_METHOD_BLIK:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_CREDITCARD:
                if ($purchaseTransactionType === Constants::TX_CAPTURE_AUTHORIZATION) {
                    $transactionType = Constants::TX_REFUND_CAPTURE;
                }
                if ($purchaseTransactionType === Constants::TX_PURCHASE) {
                    $transactionType = Constants::TX_REFUND_PURCHASE;
                }
                break;
            case Constants::PURCHASE_METHOD_IDEAL:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_P24:
                if ($purchaseTransactionType === Constants::TX_DEBIT) {
                    $transactionType = Constants::TX_REFUND_REQUEST;
                }
                break;
            case Constants::PURCHASE_METHOD_PAYPAL:
                if ($purchaseTransactionType === Constants::TX_AUTHORIZATION) {
                    $transactionType = Constants::TX_REFUND_CAPTURE;
                }
                if ($purchaseTransactionType === Constants::TX_DEBIT) {
                    $transactionType = Constants::TX_REFUND_DEBIT;
                }
                if ($purchaseTransactionType === Constants::TX_CAPTURE_AUTHORIZATION) {
                    $transactionType = Constants::TX_REFUND_CAPTURE;
                }
                break;
            case Constants::PURCHASE_METHOD_POI_PIA:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_SEPA_CREDIT:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_SEPA_DIRECT_DEBIT:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_SOFORT:
                // NO SUPPORT
                break;
        }

        if ($transactionType === null) {
            throw new \Exception("Purchase payment method {$purchasePaymentMethod} is not supported for refund");
        }

        $payload['payment'] = [
            'merchant-account-id' => [
                'value' => $merchantAccountId
            ],
            'shop' => [
                'system-name' => "wordpress-pagos",
                'system-version' => \WC_VERSION,
                'plugin-name' => "wc-getnet-redirect",
                'plugin-version' => "1.0.0",
                'integration-type' => "redirect"
            ],
            'request-id' => $requestId,
            'transaction-type' => $transactionType,
            'parent-transaction-id' => $transactionId
        ];

        if ($purchasePaymentMethod === Constants::PURCHASE_METHOD_ALIPAY_XBORDER) {
            $payload['payment']['requested-amount'] = [
                'value' => $order->get_total(),
                'currency' => $order->get_currency(),
            ];
        }

        return $payload;
    }
}
