<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;

class RefundedCallback
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
        $act = new RefundedCallback($order, $settings, $currentTransactionType, $transactionId);
        return $act->processRefund();
    }

    public function processRefund()
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

        $payload = WcOrderRefundedPayload::getPayload(
            $this->order,
            $this->settings,
            $processedBy,
            $purchaseRequestId,
            $purchasePaymentMethod,
            $purchaseTransactionType,
            $merchantAccountId,
            $transactionId
        );

        // Execute the refund
        $client = GetNetApiClient::createFromSettings($this->settings);
        $response = $client->postEngineRestPayments($payload);

        try{
            // Evaluate response
            if ($response['payment']['transaction-state'] == Constants::TX_STATE_FAILED) {
                $description = getNoteFromTransactionResponse($response);
                $this->order->add_order_note($description);
                throw new \Exception(__('Transaction state FAILED: ') . $description);
            }
        } catch (Exception $e) {
            $description = "Fail Refund";
            $this->order->add_order_note($description);
            throw new \Exception(__('Transaction state FAILED: ') . $description);
        }
       

        return $response;
    }
}
