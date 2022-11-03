<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;

class WcOrderCreditPayload
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
        $transactionId,
        $iban
    ) {
        $payload = [];
        $requestId = current_time('YmdHis') . $order->get_id();
        $transactionType = Constants::TX_CREDIT;

        $payload['payment'] = [
            'merchant-account-id' => [
                'value' => $merchantAccountId
            ],
            'request-id' => $requestId,
            'transaction-type' => $transactionType,
            'parent-transaction-id' => $transactionId,
            'shop' => [
                'system-name' => "wordpress-pagos",
                'system-version' => \WC_VERSION,
                'plugin-name' => "wc-getnet-redirect",
                'plugin-version' => "1.0.0",
                'integration-type' => "redirect"
            ],
            'requested-amount' => [
                'value' => $order->get_total(),
                'currency' => $order->get_currency(),
            ],
            'payment-methods' => [
                'payment-method' => [
                    ['name' => Constants::PURCHASE_METHOD_SEPA_CREDIT]
                ]
            ],
            'bank-account' => [
                "iban" => $iban
            ]
        ];

        return $payload;
    }
}
