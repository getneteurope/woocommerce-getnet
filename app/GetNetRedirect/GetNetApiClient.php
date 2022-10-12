<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Constants;
use App\Library\HttpClient;

class GetNetApiClient extends HttpClient
{
    public function __construct($baseUri, $headers = [
        'content-type' => 'application/json',
        'accept' => 'application/json',
    ])
    {
        parent::__construct($baseUri, $headers);
    }

    /**
     * Creates a HttpClient from the settings
     *
     * @param array $settings
     * @return \App\GetNetRedirect\GetNetApiClient
     */
    public static function createFromSettings($settings)
    {
        // Create the http client
        $baseUri = $settings['getnet_redirect_option_api_url'];
        $client = new GetNetApiClient($baseUri);
        // Set the token
        $user = $settings['getnet_redirect_option_user'];
        $password = $settings['getnet_redirect_option_password'];
        $token = base64_encode("{$user}:{$password}");
        $client->setBearerAuth($token, 'Basic');
        return $client;
    }

    /**
     * Posts the payment register
     *
     * @param array $payload
     * @return array
     */
    public function postEngineRestPayments($payload)
    {
        return $this->request('engine/rest/payments/', $payload);
    }

    /**
     * Retrieves the payment status
     *
     * @param string $merchantAccount
     * @param string $requestId
     * @return array
     */
    public function postEngineRestMerchantsPaymentsSearch($merchantAccount, $requestId)
    {
        return $this->request(
            "engine/rest/merchants/{$merchantAccount}/payments/search?payment.request-id={$requestId}",
            null,
            Constants::HTTP_METHOD_GET
        );
    }

    /**
     * Posts the payment register
     *
     * @param array $payload
     * @return array
     */
    public function postEngineRestPaymentMethods($payload)
    {
        return $this->request('engine/rest/paymentmethods/', $payload);
    }
}
