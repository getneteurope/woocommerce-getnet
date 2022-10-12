<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\GetNetRedirect;

class PurchaseCallbackDecoder
{
    /**
     * Decode data from the callback
     *
     * @return array
     */
    public function decode($merchantSecreyKey)
    {
        $responseSignatureBase64 = $_REQUEST['response-signature-base64'];
        $responseSignatureAlgorithm = $_REQUEST['response-signature-algorithm'];
        $responseBase64 = $_REQUEST['response-base64'];
        $responseBase64Decoded = base64_decode($responseBase64);
        $contentParsed = json_decode($responseBase64Decoded, true);
        $jsonError = json_last_error();
        if (!$this->isValidSignature($responseBase64, $responseSignatureBase64, $merchantSecreyKey)) {
            throw new \Exception('The signature is not valid');
        }
        if ($jsonError !== JSON_ERROR_NONE) {
            throw new \Exception('Could not parse response JSON, error: ' . $jsonError);
        }
        return $contentParsed;
    }

    /**
     * Checks if the signature is valid
     *
     * @param string $responseBase64
     * @param string $responseSignatureBase64
     * @param string $merchantSecreyKey
     * @return boolean
     */
    public function isValidSignature($responseBase64, $responseSignatureBase64, $merchantSecreyKey)
    {
        $signature = hash_hmac('sha256', $responseBase64, $merchantSecreyKey, true);
        return hash_equals($signature, base64_decode($responseSignatureBase64));
    }
}
