<?php

/**
 *
 * Copyright © 2022 PagoNxt Merchant Solutions S.L. and Santander España Merchant Services, Entidad de Pago, S.L.U.
 * All rights reserved.
 *
 */

namespace App\Library;

use App\Constants;
use App\Models\RequestLog;

class HttpClient
{
    /**
     * Base URI
     *
     * @var string
     */
    public $baseUri;

    /**
     * Headers array
     *
     * @var array
     */
    public $headers;

    public function __construct($baseUri, $headers = [
            'content-type' => 'application/json',
            'accept' => 'application/json',
        ])
    {
        $this->baseUri = $baseUri;
        $this->headers = $headers;
    }

    /**
     * Add the given key/value to the headers array
     *
     * @param string $key
     * @param mixed $val
     * @return array
     */
    protected function addToHeaders($key, $val)
    {
        $this->headers = array_merge([$key => $val], $this->headers);
        return $this->headers;
    }

    /**
     * Get the URL for the endpoint
     *
     * @param string $endpoint
     * @return string
     */
    protected function getUrl($endpoint): string
    {
        return "{$this->baseUri}{$endpoint}";
    }

    /**
     * Makes the request
     *
     * @param string $endpoint
     * @param array $payload
     * @return mixed
     */
    protected function request($endpoint, $payload, $method = 'post')
    {
        $url = $this->getUrl($endpoint);
        $params = [
            'body' => wp_json_encode($payload),
            'headers' => $this->headers,
            'timeout' => 60,
            'redirection' => 5,
            'blocking' => true,
            'httpversion' => '1.0',
            'sslverify' => false,
            'data_format' => 'body',
        ];
        $logLastId = RequestLog::create([
            'url' => $url,
            'req_headers' => json_encode($params['headers']),
            'req_body' => json_encode($payload),
        ]);
        $response = null;
        if ($method === Constants::HTTP_METHOD_POST) {
            $response = wp_remote_post($url, $params);
        } elseif ($method === Constants::HTTP_METHOD_GET) {
            $response = wp_remote_get($url, $params);
        }
        $responseBody = json_decode(wp_remote_retrieve_body($response), true);
        RequestLog::update($logLastId, [
            'res_code' => wp_remote_retrieve_response_code($response),
            'res_body' => wp_remote_retrieve_body($response)
        ]);
        return $responseBody;
    }

    /**
     * Sets the bearer token
     *
     * @param string $token
     * @param string $bearerLabel
     * @return string
     */
    protected function setBearerAuth($token, $bearerLabel = 'Bearer')
    {
        $this->addToHeaders('Authorization', "${bearerLabel} {$token}");
        return $this->headers['Authorization'];
    }
}
