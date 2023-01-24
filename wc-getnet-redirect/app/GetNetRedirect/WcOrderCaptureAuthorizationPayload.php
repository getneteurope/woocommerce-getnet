<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;

class WcOrderCaptureAuthorizationPayload
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
        $transactionType = Constants::TX_CAPTURE_AUTHORIZATION;

        $payload['payment'] = [
            'merchant-account-id' => [
                'value' => $merchantAccountId
            ],
            'shop' => [
                'system-name' => "wordpress-pagos",
                'system-version' => \WC_VERSION,
                'plugin-name' => "wc-getnet-redirect",
                'plugin-version' => "1.0.6",
                'integration-type' => "redirect"
            ],
            'request-id' => $requestId,
            'transaction-type' => $transactionType,
            'parent-transaction-id' => $transactionId,
            'payment-methods' => [
                'payment-method' => [
                    ['name' => $purchasePaymentMethod]
                ]
            ]
        ];

        return $payload;
    }
}
