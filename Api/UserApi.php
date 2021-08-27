<?php

namespace Moova\Api;

class UserApi extends ApiConnector implements ApiInterface
{
    const DEV_BASE_URL = 'https://api-dev.moova.io/';
    const PROD_BASE_URL = 'https://api-prod.moova.io/';

    public function __construct(string $environment)
    {
        $this->environment = $environment;
    }

    public function get(string $endpoint, array $body = [], array $headers = [])
    {
        $this->api_config = $this->api_config ?? [];
        $body = array_merge($this->api_config, $body);
        $url = $this->get_base_url() . $endpoint;
        return $this->exec('GET', $url, [], $headers);
    }

    public function get_base_url()
    {
        if ($this->environment === 'test') {
            return self::DEV_BASE_URL;
        }
        return self::PROD_BASE_URL;
    }
}
