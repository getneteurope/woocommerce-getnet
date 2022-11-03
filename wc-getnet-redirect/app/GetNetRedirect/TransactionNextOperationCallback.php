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
use WC_Order;

class TransactionNextOperationCallback
{
    /**
     * Transaction id
     *
     * @var int
     */
    public $transactionId;

    /**
     * Transaction object
     *
     * @var stdClass
     */
    public $transaction;

    /**
     * Requested transaction type
     *
     * @var string
     */
    public $requestedTransactionType;

    public $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->transactionId = $_GET['tx_id'];
        $this->transaction = Transaction::find($this->transactionId);
        $this->requestedTransactionType = $_GET['requested_transaction_type'];
    }

    public function show()
    {
        $order = \WC_Order_Factory::get_order($this->transaction->order_id);
        $updateOrderStatusTo = null;
        switch ($this->requestedTransactionType) {
            case Constants::TX_CAPTURE_AUTHORIZATION:
                $response = CaptureAuthorizationCallback::execute(
                    $order,
                    $this->settings,
                    $this->transaction->last_transaction_type,
                    $this->transaction->last_transaction_id
                );
                $updateOrderStatusTo = Constants::WC_STATUS_PROCESSING;
                break;
            case Constants::TX_CREDIT:
                $response = CreditCallback::execute(
                    $order,
                    $this->settings,
                    $this->transaction->last_transaction_type,
                    $this->transaction->last_transaction_id,
                    $this->transaction
                );
                $updateOrderStatusTo = Constants::WC_STATUS_REFUNDED;
                break;
            case Constants::TX_REFUND_CAPTURE:
            case Constants::TX_REFUND_DEBIT:
            case Constants::TX_REFUND_REQUEST:
            case Constants::TX_REFUND_PURCHASE:
                $response = RefundedCallback::execute(
                    $order,
                    $this->settings,
                    $this->transaction->last_transaction_type,
                    $this->transaction->last_transaction_id
                );
                $updateOrderStatusTo = Constants::WC_STATUS_REFUNDED;
                break;
            case Constants::TX_VOID_AUTHORIZATION:
            case Constants::TX_VOID_CAPTURE:
            case Constants::TX_VOID_PURCHASE:
            case Constants::TX_VOID_PENDING_DEBIT:
                $response = CancelledCallback::execute(
                    $order,
                    $this->settings,
                    $this->transaction->last_transaction_type,
                    $this->transaction->last_transaction_id
                );
                $updateOrderStatusTo = Constants::WC_STATUS_CANCELLED;
                break;
            case Constants::TX_REFRESH_STATUS:
                $response = RefreshStatusCallback::execute(
                    $order,
                    $this->settings,
                    $this->transaction->last_transaction_type,
                    $this->transaction->last_transaction_id
                );
                break;
            case Constants::TX_REFRESH_PENDING_CREDIT_STATUS:
                $response = RefreshPendingCreditStatusCallback::execute(
                    $order,
                    $this->settings,
                    $this->transaction->id
                );
                break;
        }

        $this->processResponse($order, $response, $updateOrderStatusTo);

        wp_redirect('?page=gnr_txs');
    }

    /**
     * Process the response
     *
     * @param mixed $order
     * @param array $response
     * @param string $updateOrderStatusTo
     * @return void
     */
    public function processResponse($order, $response, $updateOrderStatusTo)
    {
        // Response does not contains the correct values
        if (
            !array_key_exists('payment', $response) ||
            !array_key_exists('transaction-state', $response['payment'])
        ) {
            return null;
        }

        // Response was not succesfull
        if ($response['payment']['transaction-state'] != Constants::TX_STATE_SUCCESS) {
            return null;
        }

        // Manage all the responses
        if (
            $this->requestedTransactionType == Constants::TX_CAPTURE_AUTHORIZATION ||
            $this->requestedTransactionType == Constants::TX_CREDIT ||
            $this->requestedTransactionType == Constants::TX_REFUND_CAPTURE ||
            $this->requestedTransactionType == Constants::TX_REFUND_DEBIT ||
            $this->requestedTransactionType == Constants::TX_REFUND_REQUEST ||
            $this->requestedTransactionType == Constants::TX_REFUND_PURCHASE ||
            $this->requestedTransactionType == Constants::TX_VOID_AUTHORIZATION ||
            $this->requestedTransactionType == Constants::TX_VOID_CAPTURE ||
            $this->requestedTransactionType == Constants::TX_VOID_PURCHASE ||
            $this->requestedTransactionType == Constants::TX_VOID_PENDING_DEBIT
        ) {
            $lastTransactionId = $response['payment']['transaction-id'];
            $lastTransactionType = $response['payment']['transaction-type'];
            $lastTransactionResBody = json_encode($response);
            $this->transaction = Transaction::find($this->transactionId);
            Transaction::update($this->transaction->id, [
                'last_transaction_type' => $lastTransactionType,
                'last_transaction_res_body' => $lastTransactionResBody,
                'last_transaction_id' => $lastTransactionId
            ]);
            $note = getNoteFromTransactionResponse($response);
            $order->update_status($updateOrderStatusTo, $note);
        }

        // Manage the refund process
        if ($this->requestedTransactionType == Constants::TX_CREDIT) {
            $purchasePaymentMethod = $order->get_meta(Constants::ORDER_MK_PURCHASE_PAYMENT_METHOD);
            if (
                $purchasePaymentMethod == Constants::PURCHASE_METHOD_BLIK ||
                $purchasePaymentMethod == Constants::PURCHASE_METHOD_IDEAL ||
                $purchasePaymentMethod == Constants::PURCHASE_METHOD_SEPA_DIRECT_DEBIT ||
                $purchasePaymentMethod == Constants::PURCHASE_METHOD_SOFORT
            ) {
                $lastRefundRequestId = $response['payment']['request-id'];
                $lastRefundMerchantAccountId = $response['payment']['merchant-account-id']['value'];
                $lastTransactionId = $response['payment']['transaction-id'];
                $lastTransactionType = $response['payment']['transaction-type'];
                $lastTransactionResBody = json_encode($response);
                $this->transaction = Transaction::find($this->transactionId);
                Transaction::update($this->transaction->id, [
                    'transaction_id' => $lastTransactionId,
                    'last_transaction_type' => $lastTransactionType,
                    'last_transaction_res_body' => $lastTransactionResBody,
                    'last_transaction_id' => $lastTransactionId,
                    'last_refund_request_id' => $lastRefundRequestId,
                    'last_refund_merchant_account_id' => $lastRefundMerchantAccountId,
                ]);
                $note = getNoteFromTransactionResponse($response);
            }
        }

        // If the request was using refresh status
        if (
            $this->requestedTransactionType == Constants::TX_REFRESH_STATUS ||
            $this->requestedTransactionType == Constants::TX_REFRESH_PENDING_CREDIT_STATUS
        ) {
            // If the transaction type is different
            if ($this->transaction->last_transaction_type != $response['payment']['transaction-type']) {
                $lastTransactionId = $response['payment']['transaction-id'];
                $lastTransactionType = $response['payment']['transaction-type'];
                $lastTransactionResBody = json_encode($response);
                $this->transaction = Transaction::find($this->transactionId);
                Transaction::update($this->transaction->id, [
                    'last_transaction_type' => $lastTransactionType,
                    'last_transaction_res_body' => $lastTransactionResBody,
                    'last_transaction_id' => $lastTransactionId,
                ]);
                $note = getNoteFromTransactionResponse($response);
            }
        }
    }
}
