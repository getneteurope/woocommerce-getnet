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

class CreditCallback
{
    public function __construct($order, $settings, $currentTransactionType, $transactionId, $transaction)
    {
        $this->order = $order;
        $this->settings = $settings;
        $this->currentTransactionType = $currentTransactionType;
        $this->transactionId = $transactionId;
        $this->transaction = $transaction;
    }

    public static function execute(
        $order,
        $settings,
        $currentTransactionType = null,
        $transactionId = null,
        $transaction = null
    ) {
        $act = new CreditCallback($order, $settings, $currentTransactionType, $transactionId, $transaction);
        return $act->processCredit();
    }

    public function processCredit()
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

        if ($this->currentTransactionType) {
            $purchaseTransactionType = $this->currentTransactionType;
        }

        if ($this->transactionId) {
            $transactionId = $this->transactionId;
        }

        $merchantAccountId = $this->settings['getnet_redirect_option_msk_sc'];

        $lastPaymentResBody = json_decode($this->transaction->last_payment_res_body, true);
        $iban = $lastPaymentResBody['payment']['bank-account']['iban'];

        $payload = WcOrderCreditPayload::getPayload(
            $this->order,
            $this->settings,
            $processedBy,
            $purchaseRequestId,
            $purchasePaymentMethod,
            $purchaseTransactionType,
            $merchantAccountId,
            $transactionId,
            $iban
        );

        // Execute the refund
        $client = GetNetApiClient::createFromSettings($this->settings);
        $response = $client->postEngineRestPaymentMethods($payload);

        // Evaluate response
        if ($response['payment']['transaction-state'] == Constants::TX_STATE_FAILED) {
            $description = getNoteFromTransactionResponse($response);
            $this->order->add_order_note($description);
            throw new \Exception(__('Transaction state FAILED: ') . $description);
        }

        return $response;
    }
}
