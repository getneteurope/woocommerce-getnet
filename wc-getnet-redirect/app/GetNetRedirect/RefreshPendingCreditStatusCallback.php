<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;
use App\Models\Transaction;

class RefreshPendingCreditStatusCallback
{
    public function __construct($order, $settings, $transactionId)
    {
        $this->order = $order;
        $this->settings = $settings;
        $this->transactionId = $transactionId;
        $this->transaction = Transaction::find($this->transactionId);
    }

    public static function execute($order, $settings, $transactionId)
    {
        $act = new RefreshPendingCreditStatusCallback($order, $settings, $transactionId);
        return $act->processRefreshPendingCreditStatus();
    }

    public function processRefreshPendingCreditStatus()
    {
        $processedBy = $this->order->get_meta(Constants::ORDER_MK_PROCESSED_BY);
        $purchaseRequestId = $this->transaction->last_refund_request_id;
        $merchantAccountId = $this->transaction->last_refund_merchant_account_id;
        $purchaseTransactionType = $this->transaction->last_transaction_type;

        if ($processedBy !== Constants::ORDER_MV_PROCESSED_BY) {
            // Nothing to do since it was not processed by GetNet Plugin
            return;
        }

        // Remove text from purchase request id
        $purchaseRequestId = str_replace('-pending-debit', '', $purchaseRequestId);
        $purchaseRequestId = str_replace('-pending-credit', '', $purchaseRequestId);

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
