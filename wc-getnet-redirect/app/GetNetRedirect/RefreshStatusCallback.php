<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;

class RefreshStatusCallback
{
    public function __construct($order, $settings, $currentTransactionType, $transactionId)
    {
        $this->order = $order;
        $this->settings = $settings;
        $this->currentTransactionType = $currentTransactionType;
        $this->transactionId = $transactionId;
    }

    public static function execute($order, $settings, $currentTransactionType = null, $transactionId = null)
    {
        $act = new RefreshStatusCallback($order, $settings, $currentTransactionType, $transactionId);
        return $act->processRefreshStatus();
    }

    public function processRefreshStatus()
    {
        $processedBy = $this->order->get_meta(Constants::ORDER_MK_PROCESSED_BY);
        $purchaseRequestId = $this->order->get_meta(Constants::ORDER_MK_PURCHASE_REQUEST_ID);
        $purchasePaymentMethod = $this->order->get_meta(Constants::ORDER_MK_PURCHASE_PAYMENT_METHOD);
        $purchaseTransactionType = $this->order->get_meta(Constants::ORDER_MK_PURCHASE_TRANSACTION_TYPE);
        $merchantAccountId = $this->order->get_meta(Constants::ORDER_MK_PURCHASE_MERCHANT_ACCOUNT_ID);
        $transactionId = $this->order->get_meta(Constants::ORDER_MK_PURCHASE_TRANSACTION_ID);
        if ($processedBy !== Constants::ORDER_MV_PROCESSED_BY) {
            // Nothing to do since it was not processed by GetNet Plugin
            return;
        }

        // Execute the refund
        $client = GetNetApiClient::createFromSettings($this->settings);
        $response = $client->postEngineRestMerchantsPaymentsSearch($merchantAccountId, $purchaseRequestId);

        // Evaluate response
        if (array_key_exists('transaction-type', $response['payment'])) {
            if (
                $response['payment']['transaction-state'] == Constants::TX_STATE_SUCCESS &&
                $response['payment']['transaction-type'] !== $purchaseTransactionType
            ) {
                getNoteFromTransactionResponse($response);
                $this->order->add_order_note("Upgraded from {$purchaseTransactionType} to {$response['payment']['transaction-type']}");
            } elseif ($response['payment']['transaction-state'] == Constants::TX_STATE_SUCCESS) {
                $this->order->add_order_note("Update status requested : {$response['payment']['transaction-type']} {$response['payment']['transaction-state']}");
            }
        } else {
            $this->order->add_order_note("Update status requested: {$purchaseTransactionType}");
        }

        return $response;
    }
}
