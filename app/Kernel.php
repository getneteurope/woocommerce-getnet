<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App;

use App\GetNetRedirect\ActivationCallback;

class Kernel
{
    public static $instance;

    /**
     * Activation hook. Runs when the plugin is activated
     *
     * @return void
     */
    private function addActivationHook()
    {
        register_activation_hook(PLUGIN_FILE, function () {
            ActivationCallback::execute();
        });
    }

    /**
     * Adds the payment gateway to the list of gateways
     *
     * @return void
     */
    private function addPaymentGatewayToList()
    {
        add_filter('woocommerce_payment_gateways', function ($gateways) {
            $gateways[] = 'App\\GetNetRedirectGateway';
            return $gateways;
        });
    }

    /**
     * Initializes the plugin
     *
     * @return \App\Kernel
     */
    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Kernel();
            self::$instance->setup();
        }
        return self::$instance;
    }

    /**
     * Initializes the gateway
     *
     * @return void
     */
    private function iniatilizeGateway()
    {
        add_action(
            'plugins_loaded',
            function () {
                (new GetNetRedirectGateway());
            },
            11
        );
    }

    /**
     * Setups the plugin
     *
     * @return void
     */
    public function setup()
    {
        $this->addActivationHook();
        $this->addPaymentGatewayToList();
        $this->iniatilizeGateway();
    }
}
