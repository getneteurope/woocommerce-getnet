<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;
use App\Models\RequestLog;
use App\Models\Transaction;

class PurchaseCallback
{
    /**
     * Executes the order process
     *
     * @param array $settings
     * @return array
     */
    public static function execute($settings)
    {
        // Check if the key exists, if no key exists then consider as a failed order
        if (
            array_key_exists('response-signature-base64', $_REQUEST) === false ||
            array_key_exists('response-signature-algorithm', $_REQUEST) === false ||
            array_key_exists('response-base64', $_REQUEST) === false
        ) {
            // Manage as a failed payment
            $orderId = $_REQUEST['order_id'];
            $order = \WC_Order_Factory::get_order($orderId);
            $order->update_status('failed', 'Payment failed');
            $response = [
                'success' => false,
                'message' => __('Payment failed', 'wc_getnet_redirect'),
                'order' => $order,
            ];
            return $response;
        }

        // Decode the response of getnet
        $decoder = new PurchaseCallbackDecoder();
        $decodedResponse = $decoder->decode($settings['getnet_redirect_option_msk']);

        // Store response
        RequestLog::create(['url' => fullUrl(), 'req_body' => json_encode($decodedResponse)]);

        // Get the order that's encoded on the request id
        $requestId = $decodedResponse['payment']['request-id'];
        $orderId = substr($requestId, 14);

        // Get payment method before decoded response because SEPA direct appends attributes to the request id
        $paymentMethod = $decodedResponse['payment']['payment-methods']['payment-method'][0]['name'];
        if ($paymentMethod == Constants::PURCHASE_METHOD_SEPA_DIRECT_DEBIT) {
            $orderId = str_ireplace('-pending-debit', '', $orderId);
            $orderId = str_ireplace('-authorization', '', $orderId);
        }

        if (str_contains($requestId, '-')) {
                  $arrayBody = explode("-", $requestId);
                  $requestId  = $arrayBody[0];
         }


        // Check that the order exists
        $order = \WC_Order_Factory::get_order($orderId);
        if (is_null($order) || empty($order)) {
            $response = [
                'success' => false,
                'message' => __('The order does not exists', 'wc_getnet_redirect'),
                'order' => null,
            ];
            return $response;
        }
        $response = ['success' => false, 'message' => null, 'order' => null];

        // Updates the order metadata
        $transactionType = $decodedResponse['payment']['transaction-type'];
        $merchantAccountId = $decodedResponse['payment']['merchant-account-id']['value'];
        $transactionId = $decodedResponse['payment']['transaction-id'];
        $order->update_meta_data(Constants::ORDER_MK_PROCESSED_BY, Constants::ORDER_MV_PROCESSED_BY);
        $order->update_meta_data(Constants::ORDER_MK_PURCHASE_REQUEST_ID, $requestId);
        $order->update_meta_data(Constants::ORDER_MK_PURCHASE_PAYMENT_METHOD, $paymentMethod);
        $order->update_meta_data(Constants::ORDER_MK_PURCHASE_TRANSACTION_TYPE, $transactionType);
        $order->update_meta_data(Constants::ORDER_MK_PURCHASE_MERCHANT_ACCOUNT_ID, $merchantAccountId);
        $order->update_meta_data(Constants::ORDER_MK_PURCHASE_TRANSACTION_ID, $transactionId);
        $order->save_meta_data();

        // Set order status given the transacion state
        $transactionState = $decodedResponse['payment']['transaction-state'];
        if ($transactionState == 'success') {
            if (!$order->is_paid()) {
                $order->payment_complete($requestId);
                $order->update_status('processing');

                // Stores the transaction
                Transaction::create([
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'payment_method' => $paymentMethod,
                    'last_transaction_type' => $transactionType,
                    'last_transaction_res_body' => null,
                    'last_transaction_id' => $transactionId,
                    'last_payment_res_body' => json_encode($decodedResponse)
                ]);

                $response = [
                    'success' => true,
                    'message' => __('The order was paid', 'wc_getnet_redirect'),
                    'order' => $order,
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => __('The order was already payed', 'wc_getnet_redirect'),
                    'order' => $order,
                ];
            }
        } elseif ($transactionState == 'failed') {
            $order->update_status('failed', 'Payment failed');
            $response = [
                'success' => false,
                'message' => __('Payment failed', 'wc_getnet_redirect'),
                'order' => $order,
            ];
        } elseif ($transactionState == 'cancel') {
            $response = [
                'success' => false,
                'message' => __('Payment canceled', 'wc_getnet_redirect'),
                'order' => $order,
            ];
        }

        if ($order) {
            // Stores the order description from the callback
            $description = getNoteFromTransactionResponse($decodedResponse);
            $order->add_order_note($description);
        }

        return $response;
    }
}
