<?php

namespace Ecomerciar\Moova\Api;

class MoovaApi extends ApiConnector implements ApiInterface
{
    const BASE_URL = 'https://api-dev.moova.io/b2b';

    public function __construct(string $clientid, string $client_secret)
    {
        $this->api_config = [
            'appId' => $clientid,
        ];
        $this->auth_header = $client_secret;
    }

    public function get(string $endpoint, array $body = [], array $headers = [])
    {
        $body = array_merge($this->api_config, $body);
        $url = $this->get_base_url() . $endpoint;
        $headers['Authorization'] = $this->auth_header;
        if (!empty($body)) {
            $url .= '?' . http_build_query($body);
        }
        return $this->exec('GET', $url, [], $headers);
    }

    public function post(string $endpoint, array $body = [], array $headers = [])
    {
        $url = $this->get_base_url() . $endpoint;
        $url = $this->add_params_to_url($url, http_build_query($this->api_config));
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $this->auth_header;
        return $this->exec('POST', $url, $body, $headers);
    }

    public function put(string $endpoint, array $body = [], array $headers = [])
    {
        $url = $this->get_base_url() . $endpoint;
        $url = $this->add_params_to_url($url, http_build_query($this->api_config));
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $this->auth_header;
        return $this->exec('PUT', $url, $body, $headers);
    }

    public function delete(string $endpoint, array $body = [], array $headers = [])
    {
        $url = $this->get_base_url() . $endpoint;
        $url = $this->add_params_to_url($url, http_build_query($this->api_config));
        $headers['Content-Type'] = 'application/json';
        $headers['Authorization'] = $this->auth_header;
        return $this->exec('DELETE', $url, $body, $headers);
    }

    public function get_base_url()
    {
        return self::BASE_URL;
    }
}
