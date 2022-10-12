<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;

class WcOrderCancelledPayload
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
        $currentTransactionType,
        $merchantAccountId,
        $transactionId
    ) {
        $payload = [];
        $requestId = current_time('YmdHis') . $order->get_id();
        $transactionType = null;

        // Transaction Type Logic
        switch ($purchasePaymentMethod) {
            case Constants::PURCHASE_METHOD_ALIPAY_XBORDER:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_BLIK:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_CREDITCARD:
                if ($currentTransactionType === Constants::TX_AUTHORIZATION) {
                    $transactionType = Constants::TX_VOID_AUTHORIZATION;
                }
                if ($currentTransactionType === Constants::TX_CAPTURE_AUTHORIZATION) {
                    $transactionType = Constants::TX_VOID_CAPTURE;
                }
                if ($currentTransactionType === Constants::TX_PURCHASE) {
                    $transactionType = Constants::TX_VOID_PURCHASE;
                }
                break;
            case Constants::PURCHASE_METHOD_IDEAL:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_P24:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_PAYPAL:
                if ($currentTransactionType === Constants::TX_AUTHORIZATION) {
                    $transactionType = Constants::TX_VOID_AUTHORIZATION;
                }
                break;
            case Constants::PURCHASE_METHOD_POI_PIA:
                if ($currentTransactionType === Constants::TX_AUTHORIZATION) {
                    $transactionType = Constants::TX_VOID_AUTHORIZATION;
                }
                break;
            case Constants::PURCHASE_METHOD_SEPA_CREDIT:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_SEPA_DIRECT_DEBIT:
                if ($currentTransactionType === Constants::TX_PENDING_DEBIT) {
                    $transactionType = Constants::TX_VOID_PENDING_DEBIT;
                }
                break;
            case Constants::PURCHASE_METHOD_SOFORT:
                // NO SUPPORT
                break;
        }

        if ($transactionType === null) {
            throw new \Exception("Purchase payment method {$purchasePaymentMethod} is not supported for cancellation");
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
            'ip-address' => '127.0.0.1',
            'parent-transaction-id' => $transactionId
        ];
        return $payload;
    }
}
