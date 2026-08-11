<?php

namespace MollieForms\Integrations\Rcur;

use MollieForms\Exception;
use MollieForms\MollieForms;

class RcurApi
{

    const API_URL = 'https://rcur.app/api/v2/';

    private $apiKey, $mollieForms;

    public function __construct($apiKey)
    {
        $this->apiKey      = $apiKey;
        $this->mollieForms = new MollieForms();
    }

    /**
     * @throws Exception
     */
    public function post($endpoint, array $params)
    {
        return $this->performCall('POST', self::API_URL . ltrim($endpoint, '/'), $params);
    }

    /**
     * @throws Exception
     */
    public function get($endpoint, array $params = [])
    {
        $url = self::API_URL . ltrim($endpoint, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->performCall('GET', $url);
    }

    /**
     * @throws Exception
     */
    private function performCall($method, $url, $body = null)
    {
        if (empty($this->apiKey)) {
            throw new Exception('No RCUR API-key is set');
        }

        $args = [
            'method'     => $method,
            'timeout'    => 30,
            'blocking'   => true,
            'headers'    => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            'user-agent' => 'PHP/' . phpversion() . ' Wordpress/' . get_bloginfo('version') .
                            ' MollieForms/' . $this->mollieForms->getVersion() . ' Rcur',
            'body'       => $body !== null ? wp_json_encode($body) : null,
        ];

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new Exception('RCUR request failed: ' . $response->get_error_message());
        }

        $code   = wp_remote_retrieve_response_code($response);
        $object = @json_decode(wp_remote_retrieve_body($response));

        if ($code >= 400) {
            $message = 'HTTP ' . $code;
            if (is_object($object) && isset($object->message)) {
                $message .= ' - ' . $object->message;
            } elseif (is_object($object) && isset($object->error)) {
                $message .= ' - ' . (is_string($object->error) ? $object->error : wp_json_encode($object->error));
            }
            throw new Exception('RCUR error: ' . $message);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Unable to decode RCUR response.');
        }

        return $object;
    }

    public function createCustomer(array $payload)
    {
        return $this->post('customers', $payload);
    }

    public function createSubscription(array $payload)
    {
        return $this->post('subscriptions', $payload);
    }

    public function listCustomers()
    {
        return $this->get('customers');
    }

    /**
     * Returns the Mollie profiles connected to the RCUR account.
     * RCUR wraps the list in a { "data": [...] } envelope; this unwraps it so
     * callers always get a plain array of profile objects.
     *
     * @return array
     * @throws Exception
     */
    public function listMollieProfiles()
    {
        $response = $this->get('organizations/mollie-profiles');

        if (is_object($response) && isset($response->data) && is_array($response->data)) {
            return $response->data;
        }

        return is_array($response) ? $response : [];
    }
}
