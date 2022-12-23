<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App;

use App\GetNetRedirect\CancelledCallback;
use App\GetNetRedirect\LogIndexCallback;
use App\GetNetRedirect\PurchaseCallback;
use App\GetNetRedirect\RefundedCallback;
use App\GetNetRedirect\TransactionIndexCallback;
use App\GetNetRedirect\TransactionNextOperationCallback;
use App\GetNetRedirect\TransactionShowCallback;
use App\GetNetRedirect\UrlRedirectionEngine;

class GetNetRedirectGateway extends \WC_Payment_Gateway
{
    /**
     * Shows the log page, by default use false (don't show)
     */
    public const SHOW_LOG_PAGE = false;

    public function __construct()
    {
        // Setups the minimal attributes for a payment gateway
        $this->id = Constants::PAYMENT_GATEWAY_ID;
        $this->has_fields = true;
        $this->method_title = __(Constants::PAYMENT_GATEWAY_METHOD_TITLE, 'wc_getnet_redirect');
        $this->method_description = __(Constants::PAYMENT_GATEWAY_METHOD_DESCRIPTION, 'wc_getnet_redirect');
        $this->title = __(CONSTANTS::PAYMENT_GATEWAY_CHECKOUT_TITLE, 'wc_getnet_redirect');
        $this->description = __(CONSTANTS::PAYMENT_GATEWAY_CHECKOUT_DESCRIPTION, 'wc_getnet_redirect');

        // Setups the form fields
        $this->setupFormFields();

        // Load settings
        $this->loadSettingsFromFormFields();

        // Setup the callback api
        $this->setupRedirectCallback();

        // Setups the menu pages
        $this->setupMenuAction();
    }

    /**
     * Retrieve the form fields
     *
     * @return array
     */
    private function getFormFields()
    {
        return [
            'enabled' => [
                'title' => __('Enable gateway', 'wc_getnet_redirect'),
                'type' => 'checkbox',
                'label' => __('Enable gateway', 'wc_getnet_redirect'),
                'default' => 'yes',
            ],
            'getnet_redirect_option_testing_mode' => [
                'title' => __('Enable testing mode', 'wc_getnet_redirect'),
                'type' => 'checkbox',
                'default' => 'yes',
            ],
            'getnet_redirect_option_user' => [
                'title' => __('User', 'wc_getnet_redirect'),
                'type' => 'text',
            ],
            'getnet_redirect_option_password' => [
                'title' => __('Password', 'wc_getnet_redirect'),
                'type' => 'password',
            ],
            'getnet_redirect_option_marc' => [
                'title' => __('Merchant account resolver category', 'wc_getnet_redirect'),
                'type' => 'text',
            ],
            'getnet_redirect_option_msk' => [
                'title' => __('Merchant Secret Key', 'wc_getnet_redirect'),
                'type' => 'text',
            ],
            'getnet_redirect_option_msk_sc' => [
                'title' => __('SEPA Credit MAID', 'wc_getnet_redirect'),
                'type' => 'text',
            ],
            'getnet_redirect_option_creditor_id' => [
                'title' => __('Creditor ID', 'wc_getnet_redirect'),
                'type' => 'text'
            ]
        ];
    }

    /**
     * Loads settings from the form fields and shows them in the menu
     *
     * @return void
     */
    private function loadSettingsFromFormFields()
    {
        // Init settings
        $this->init_settings();

        if ($this->settings['getnet_redirect_option_testing_mode'] === 'yes') {
            $this->settings['getnet_redirect_option_payment_url'] = 'https://paymentpage-test.getneteurope.com/';
        } else {
            $this->settings['getnet_redirect_option_payment_url'] = 'https://paymentpage.getneteurope.com/';
        }

        if ($this->settings['getnet_redirect_option_testing_mode'] === 'yes') {
            $this->settings['getnet_redirect_option_api_url'] = 'https://api-test.getneteurope.com/';
        } else {
            $this->settings['getnet_redirect_option_api_url'] = 'https://api.getneteurope.com/';
        }

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     * Process the payment
     *
     * @param mixed $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        // Fetches the woocommerce order
        $order = wc_get_order($order_id);
        // Generates the URL for redirection
        $redirectToUrl = UrlRedirectionEngine::execute($order, $this->settings);
        return ['result' => 'success', 'redirect' => $redirectToUrl];
    }

    /**
     * Setups the order cancelled action
     *
     * @return void
     */
    private function setupCancelledOrderAction()
    {
        add_action('woocommerce_order_status_cancelled', function ($order_id) {
            $order = wc_get_order($order_id);
            $now = new \WC_DateTime('now', $order->get_date_paid()->getTimezone());
            if ($order->get_date_paid()->format('Y-m-d') !== $now->format('Y-m-d')) {
                RefundedCallback::execute($order, $this->settings);
            } else {
                CancelledCallback::execute($order, $this->settings);
            }
        });
    }

    /**
     * Setups the menu actions
     *
     * @return voiid
     */
    private function setupMenuAction()
    {
        add_action('admin_menu', function () {
            add_submenu_page(
                'woocommerce',
                __('GetNet Txs', 'wc_getnet_redirect'),
                __('GetNet Txs', 'wc_getnet_redirect'),
                'manage_woocommerce',
                'gnr_txs',
                function () {
                    $txIndex = new TransactionIndexCallback();
                    $txIndex->show();
                },
                89
            );

            if (GetNetRedirectGateway::SHOW_LOG_PAGE) {
                add_submenu_page(
                    'woocommerce',
                    __('GetNet Log', 'wc_getnet_redirect'),
                    __('GetNet Log', 'wc_getnet_redirect'),
                    'manage_woocommerce',
                    'gnr_log',
                    function () {
                        $logIndex = new LogIndexCallback();
                        $logIndex->show();
                    },
                    90
                );
            }

            add_submenu_page(
                null,
                __('GetNet Detail', 'wc_getnet_redirect'),
                __('GetNet Detail', 'wc_getnet_redirect'),
                'manage_woocommerce',
                'gnr_detail',
                function () {
                    $transactionShow = new TransactionShowCallback();
                    $transactionShow->show();
                }
            );
            add_submenu_page(
                null,
                __('GetNet Detail', 'wc_getnet_redirect'),
                __('GetNet Detail', 'wc_getnet_redirect'),
                'manage_woocommerce',
                'gnr_next_operation',
                function () {
                    $transactionNextOperation = new TransactionNextOperationCallback($this->settings);
                    $transactionNextOperation->show();
                }
            );
        });
    }

    /**
     * Setups the order refunded action
     *
     * @return void
     */
    private function setupRefundedOrderAction()
    {
        add_action('woocommerce_order_status_refunded', function ($order_id) {
            $order = wc_get_order($order_id);
            RefundedCallback::execute($order, $this->settings);
        });
    }

    /**
     * Setups the form fields
     *
     * @return void
     */
    private function setupFormFields()
    {
        $this->form_fields = $this->getFormFields();
    }

    /**
     * Setups the redirect callback
     *
     * @return void
     */
    private function setupRedirectCallback()
    {
        add_action('woocommerce_api_getnet-redirect-callback', array($this, 'redirectCallback'));
    }

    /**
     * Executes the callback operations
     *
     * @return void
     */
    public function redirectCallback()
    {
        $result = PurchaseCallback::execute($this->settings);
        wp_redirect($result['order']->get_checkout_order_received_url());
    }
}
