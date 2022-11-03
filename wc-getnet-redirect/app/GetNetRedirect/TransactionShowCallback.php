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

class TransactionShowCallback
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

    public function __construct()
    {
        $this->transactionId = $_GET['tx_id'];
        $this->transaction = Transaction::find($this->transactionId);
    }

    /**
     * Get the next operation url
     *
     * @param string $nextTransactionType
     * @return string
     */
    private function getNextOperationUrl($nextTransactionType)
    {
        $result = '?page=gnr_next_operation&tx_id=' . $this->transaction->id . '&requested_transaction_type=' . $nextTransactionType;
        return $result;
    }

    /**
     * Fetches the order post operations
     *
     * @return array
     */
    private function getPostOperations()
    {
        $order = \WC_Order_Factory::get_order($this->transaction->order_id);
        $postOperations = [];
        switch ($this->transaction->payment_method) {
            case Constants::PURCHASE_METHOD_ALIPAY_XBORDER:
                if ($this->transaction->last_transaction_type == Constants::TX_DEBIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFUND_DEBIT),
                        'label' => __('Refund', 'wc_getnet_redirect'),
                    ];
                }
                break;
            case Constants::PURCHASE_METHOD_BLIK:
                if ($this->transaction->last_transaction_type == Constants::TX_DEBIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_CREDIT),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                break;
            case Constants::PURCHASE_METHOD_CREDITCARD:
                if ($this->transaction->last_transaction_type == Constants::TX_AUTHORIZATION) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_capture',
                        'link' => $this->getNextOperationUrl(Constants::TX_CAPTURE_AUTHORIZATION),
                        'label' => __('Capture', 'wc_getnet_redirect')
                    ];
                    $postOperations[] = [
                        'id' => 'getnet_btn_cancel',
                        'link' => $this->getNextOperationUrl(Constants::TX_VOID_AUTHORIZATION),
                        'label' => __('Cancel', 'wc_getnet_redirect')
                    ];
                }
                if ($this->transaction->last_transaction_type == Constants::TX_CAPTURE_AUTHORIZATION) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_cancel',
                        'link' => $this->getNextOperationUrl(Constants::TX_VOID_CAPTURE),
                        'label' => __('Cancel', 'wc_getnet_redirect')
                    ];
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFUND_CAPTURE),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                if ($this->transaction->last_transaction_type == Constants::TX_PURCHASE) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_cancel',
                        'link' => $this->getNextOperationUrl(Constants::TX_VOID_PURCHASE),
                        'label' => __('Cancel', 'wc_getnet_redirect')
                    ];
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFUND_PURCHASE),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                break;
            case Constants::PURCHASE_METHOD_IDEAL:
                if ($this->transaction->last_transaction_type == Constants::TX_DEBIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_CREDIT),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                if ($this->transaction->last_transaction_type == Constants::TX_PENDING_CREDIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refresh_pending_credit_status',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFRESH_PENDING_CREDIT_STATUS),
                        'label' => __('Update Status', 'wc_getnet_redirect')
                    ];
                }
                break;
            case Constants::PURCHASE_METHOD_P24:
                if ($this->transaction->last_transaction_type == Constants::TX_DEBIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFUND_REQUEST),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                break;
            case Constants::PURCHASE_METHOD_PAYPAL:
                if ($this->transaction->last_transaction_type == Constants::TX_AUTHORIZATION) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_capture',
                        'link' => $this->getNextOperationUrl(Constants::TX_CAPTURE_AUTHORIZATION),
                        'label' => __('Capture', 'wc_getnet_redirect')
                    ];
                    $postOperations[] = [
                        'id' => 'getnet_btn_cancel',
                        'link' => $this->getNextOperationUrl(Constants::TX_VOID_AUTHORIZATION),
                        'label' => __('Cancel', 'wc_getnet_redirect')
                    ];
                }
                if ($this->transaction->last_transaction_type == Constants::TX_CAPTURE_AUTHORIZATION) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFUND_CAPTURE),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                if ($this->transaction->last_transaction_type == Constants::TX_DEBIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFUND_DEBIT),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                break;
            case Constants::PURCHASE_METHOD_POI_PIA:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_SEPA_CREDIT:
                // NO SUPPORT
                break;
            case Constants::PURCHASE_METHOD_SEPA_DIRECT_DEBIT:
                if ($this->transaction->last_transaction_type == Constants::TX_DEBIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_CREDIT),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                if ($this->transaction->last_transaction_type == Constants::TX_PENDING_DEBIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_cancel',
                        'link' => $this->getNextOperationUrl(Constants::TX_VOID_PENDING_DEBIT),
                        'label' => __('Cancel', 'wc_getnet_redirect')
                    ];
                    $postOperations[] = [
                        'id' => 'getnet_btn_refresh_status',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFRESH_STATUS),
                        'label' => __('Update Status', 'wc_getnet_redirect')
                    ];
                }
                if ($this->transaction->last_transaction_type == Constants::TX_PENDING_CREDIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refresh_pending_credit_status',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFRESH_PENDING_CREDIT_STATUS),
                        'label' => __('Update Status', 'wc_getnet_redirect')
                    ];
                }
                break;
            case Constants::PURCHASE_METHOD_SOFORT:
                if ($this->transaction->last_transaction_type == Constants::TX_DEBIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refund',
                        'link' => $this->getNextOperationUrl(Constants::TX_CREDIT),
                        'label' => __('Refund', 'wc_getnet_redirect')
                    ];
                }
                if ($this->transaction->last_transaction_type == Constants::TX_PENDING_CREDIT) {
                    $postOperations[] = [
                        'id' => 'getnet_btn_refresh_pending_credit_status',
                        'link' => $this->getNextOperationUrl(Constants::TX_REFRESH_PENDING_CREDIT_STATUS),
                        'label' => __('Update Status', 'wc_getnet_redirect')
                    ];
                }
                break;
        }
        return $postOperations;
    }

    public function show()
    {
        $postOperations = $this->getPostOperations();
        echo "<div class=\"wrap\">
                <h1 class=\"wp-heading-inline\">Transaction detail</h1>
        ";
        foreach ($postOperations as $postOperation) {
            echo "<a id=\"{$postOperation['id']}\" href=\"{$postOperation['link']}\" class=\"page-title-action\">{$postOperation['label']}</a>";
        }
        echo "<hr class=\"wp-header-end\">";
        echo "<div class=\"postbox\" style=\"padding: 1em;\">";
        echo "
            <table>
                <tr>
                    <th style=\"text-align: left;\">Order ID</th>
                    <td>{$this->transaction->order_id}</td>
                </tr>
                <tr>
                    <th style=\"text-align: left;\">Transaction ID</th>
                    <td>{$this->transaction->transaction_id}</td>
                </tr>
                <tr>
                    <th style=\"text-align: left;\">Payment method</th>
                    <td>{$this->transaction->payment_method}</td>
                </tr>
                <tr>
                    <th style=\"text-align: left;\">Last transaction type</th>
                    <td>{$this->transaction->last_transaction_type}</td>
                </tr>
            </table>
        ";
        echo "</div>";
        echo "</div>";
    }
}