<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

use App\Library\HttpClient;

class GetNetPaymentPageClient extends HttpClient
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
     * @return \App\GetNetRedirect\GetNetPaymentPageClient
     */
    public static function createFromSettings($settings)
    {
        // Create the http client
        $baseUri = $settings['getnet_redirect_option_payment_url'];
        $client = new GetNetPaymentPageClient($baseUri);
        // Set the token
        $user = $settings['getnet_redirect_option_user'];
        $password = $settings['getnet_redirect_option_password'];


        $password = urldecode($settings['getnet_redirect_option_password']);
        $password = str_replace("&amp;","&",$password);
        $password = str_replace("&#36;","$",$password);
        $password = str_replace("&#35;","#",$password);
        $password = str_replace("&#33;","!",$password);
        $password = str_replace("&#61;","=",$password);
        $password = str_replace("&#42;","*",$password);
        $password = str_replace("&#64;","@",$password);
        $password = str_replace("&ntilde;","ñ",$password);
        $password = str_replace("&Ntilde;","Ñ",$password);

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
    public function postPaymentRegister($payload)
    {
        return $this->request('api/payment/register', $payload);
    }
}
