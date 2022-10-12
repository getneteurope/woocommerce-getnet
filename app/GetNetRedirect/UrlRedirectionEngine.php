<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

class UrlRedirectionEngine
{
    public function __construct($order, $settings)
    {
        $this->order = $order;
        $this->settings = $settings;
    }

    /**
     * Fetches the url where the user will be redirected
     *
     * @param mixed $order
     * @param mixed $settings
     * @return array
     */
    public static function execute($order, $settings)
    {
        $UrlRedirectionEngine = new UrlRedirectionEngine($order, $settings);
        return $UrlRedirectionEngine->getRedirectUrl();
    }

    /**
     * Fetches the url
     *
     * @return array
     */
    public function getRedirectUrl()
    {
        // Generate the payload for redirection
        $payload = WcOrderToRedirectionPayload::getPayload(
            $this->order,
            $this->settings['getnet_redirect_option_marc'],
            $this->settings['getnet_redirect_option_creditor_id'],
        );
        $client = GetNetPaymentPageClient::createFromSettings($this->settings);
        $response = $client->postPaymentRegister($payload);
        if (!array_key_exists('payment-redirect-url', $response)) {
            throw new \Exception('Error generating the GetNet Redirect Url');
        }
        return $response['payment-redirect-url'];
    }
}