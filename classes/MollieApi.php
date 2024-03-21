<?php

namespace MollieForms;

class MollieApi
{

    const API_URL = "https://api.mollie.com/v2/";

    private $mollieForms, $apiKey;

    /**
     * MollieApi constructor.
     *
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->setApiKey($apiKey);
        $this->mollieForms = new MollieForms();
    }

    /**
     * @param $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param       $endpoint
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function post($endpoint, array $params)
    {
        return $this->performCall('POST', self::API_URL . $endpoint, $params);
    }

    /**
     * @param       $endpoint
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function get($endpoint, array $params = [])
    {
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        return $this->performCall('GET', self::API_URL . $endpoint);
    }

    /**
     * @param       $endpoint
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function all($endpoint, array $params = [])
    {
        $resource = explode('/', $endpoint);
        $resource = end($resource);

        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        return $this->performCall('GET', self::API_URL . $endpoint)->_embedded->{$resource};
    }

    /**
     * @param       $endpoint
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function delete($endpoint, array $params = [])
    {
        return $this->performCall('DELETE', self::API_URL . $endpoint, $params);
    }

    /**
     * @param            $httpMethod
     * @param            $url
     * @param null|array $body
     *
     * @return mixed
     * @throws Exception
     */
    private function performCall($httpMethod, $url, $body = null)
    {
        if (empty($this->apiKey)) {
            throw new Exception('No API-key is set');
        }

        $args = [
            'method'     => $httpMethod,
            'timeout'    => 45,
            'blocking'   => true,
            'headers'    => ['Authorization' => 'Bearer ' . $this->apiKey],
            'user-agent' => 'PHP/' . phpversion() . ' Wordpress/' . get_bloginfo('version') . ' MollieForms/' .
                            $this->mollieForms->getVersion(),
            'body'       => $body,
        ];

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new Exception(esc_html(sanitize_text_field($response->get_error_message())));
        }

        return $this->parseResponse($response);
    }

    /**
     * @param $response
     *
     * @return mixed
     * @throws Exception
     */
    private function parseResponse($response)
    {
        $object = @json_decode($response['body']);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Unable to decode Mollie response.");
        }

        // API error
        if (isset($object->status) && isset($object->title) && isset($object->detail)) {
            throw new Exception("Mollie error: " . esc_html(sanitize_text_field($object->title . " - " . $object->detail)));
        }

        return $object;
    }
}
