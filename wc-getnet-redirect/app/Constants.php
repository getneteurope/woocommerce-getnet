<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App;

class Constants
{
    public const DEFAULT_LANGUAGE = 'en';
    public const HTTP_METHOD_GET = 'get';
    public const HTTP_METHOD_POST = 'post';
    public const PAYMENT_GATEWAY_ID = 'getnet-redirect';
    public const PAYMENT_GATEWAY_ICON = '';
    public const PAYMENT_GATEWAY_METHOD_TITLE = 'GetNet';
    public const PAYMENT_GATEWAY_METHOD_DESCRIPTION = 'Integration module for Getnet payment';
    public const PAYMENT_GATEWAY_CHECKOUT_TITLE = 'Pay by card or other payment methods';
    public const PAYMENT_GATEWAY_CHECKOUT_DESCRIPTION = '';
    public const ORDER_MK_PROCESSED_BY = 'gnr_mk_processed_by';
    public const ORDER_MV_PROCESSED_BY = 'GETNET';
    public const ORDER_MK_PURCHASE_REQUEST_ID = 'gnr_mk_purchase_id';
    public const ORDER_MK_PURCHASE_PAYMENT_METHOD = 'gnr_mk_purchase_payment_method';
    public const ORDER_MK_PURCHASE_TRANSACTION_TYPE = 'gnr_mk_purchase_transaction_type';
    public const ORDER_MK_PURCHASE_MERCHANT_ACCOUNT_ID = 'gnr_mk_purchase_merchant_account_id';
    public const ORDER_MK_PURCHASE_TRANSACTION_ID = 'gnr_mk_purchase_transaction_id';
    public const PURCHASE_METHOD_ALIPAY_XBORDER = 'alipay-xborder';
    public const PURCHASE_METHOD_BLIK = 'blik';
    public const PURCHASE_METHOD_CREDITCARD = 'creditcard';
    public const PURCHASE_METHOD_IDEAL = 'ideal';
    public const PURCHASE_METHOD_P24 = 'p24';
    public const PURCHASE_METHOD_PAYPAL = 'paypal';
    public const PURCHASE_METHOD_POI_PIA = 'wiretransfer';
    public const PURCHASE_METHOD_SEPA_CREDIT = 'sepacredit';
    public const PURCHASE_METHOD_SEPA_DIRECT_DEBIT = 'sepadirectdebit';
    public const PURCHASE_METHOD_SOFORT = 'sofortbanking';
    public const TX_AUTHORIZATION = 'authorization';
    public const TX_CAPTURE_AUTHORIZATION = 'capture-authorization';
    public const TX_CREDIT = 'credit';
    public const TX_DEBIT = 'debit';
    public const TX_PENDING_CREDIT = 'pending-credit';
    public const TX_PENDING_DEBIT = 'pending-debit';
    public const TX_PURCHASE = 'purchase';
    public const TX_REFRESH_STATUS = 'refresh-status';
    public const TX_REFRESH_PENDING_CREDIT_STATUS = 'refresh-pending-credit-status';
    public const TX_REFUND_CAPTURE = 'refund-capture';
    public const TX_REFUND_DEBIT = 'refund-debit';
    public const TX_REFUND_PURCHASE = 'refund-purchase';
    public const TX_REFUND_REQUEST = 'refund-request';
    public const TX_VOID_CAPTURE = 'void-capture';
    public const TX_VOID_AUTHORIZATION = 'void-authorization';
    public const TX_VOID_PURCHASE = 'void-purchase';
    public const TX_VOID_PENDING_DEBIT = 'void-pending-debit';
    public const TX_STATE_FAILED = 'failed';
    public const TX_STATE_SUCCESS = 'success';
    public const ZLOTY_ISO_CODE = 'PLN';
    public const WC_STATUS_PENDING = 'wc-pending';
    public const WC_STATUS_PROCESSING = 'wc-processing';
    public const WC_STATUS_PROCESSING_NO_PREFFIX = 'processing';
    public const WC_STATUS_ON_HOLD = 'wc-on-hold';
    public const WC_STATUS_COMPLETED = 'wc-completed';
    public const WC_STATUS_CANCELLED = 'wc-cancelled';
    public const WC_STATUS_REFUNDED = 'wc-refunded';
    public const WC_STATUS_FAILED = 'wc-failed';
}
