<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

if (!function_exists('dump')) {
    function dump($var)
    {
        if (!is_string($var)) {
            $var = json_encode($var);
        }
        var_dump($var);
        error_log($var);
    }
}

if (!function_exists('dumpExit')) {
    function dumpExit($var)
    {
        if (!is_string($var)) {
            $var = json_encode($var);
        }
        var_dump($var);
        error_log($var);
        exit(0);
    }
}

/**
 * Fetch full url
 *
 * @param array $s
 * @param boolean $use_forwarded_host
 * @return string
 */
function fullUrl($use_forwarded_host = false)
{
    return urlOrigin($_SERVER, $use_forwarded_host) . $_SERVER['REQUEST_URI'];
}

/**
 * Fetch url origin
 *
 * @param array $s
 * @param boolean $use_forwarded_host
 * @return string
 */
function urlOrigin($s, $use_forwarded_host = false)
{
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

/**
 * Generate the note from the transaction response
 *
 * @param mixed $response
 * @return string
 */
function getNoteFromTransactionResponse($response)
{
    $note = null;

    $requestId = $response['payment']['request-id'];

    if (str_contains($response['payment']['request-id'], '-')) {
                  $arrayBody = explode("-", $requestId);
                  $requestId  = $arrayBody[0];
         }

    if (
        array_key_exists('request-id', $response['payment']) &&
        array_key_exists('statuses', $response['payment']) &&
        array_key_exists('status', $response['payment']['statuses']) &&
        count($response['payment']['statuses']['status']) > 0
    ) {
        $note .= 'Request ID: ' . $requestId . ' // ';
        foreach ($response['payment']['statuses']['status'] as $info) {
            if (array_key_exists('code', $info) && array_key_exists('description', $info) && !str_contains($info, '201.0000')) {
                $note .= "{$info['code']}: {$info['description']}.";
            }
        }
    }
    return $note;
}
