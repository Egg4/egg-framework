<?php

namespace Egg\Authenticator;

use Egg\Yolk\Jwt;

class Token extends AbstractAuthenticator
{
    protected $cache;

    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'algorithm'     => 'HS256',
        ], $settings));

        if (!isset($this->settings['secret'])) {
            throw new \Exception('Token secret not set');
        }

        if (strlen($this->settings['secret']) < 32) {
            throw new \Exception('Token secret length < 32 chars');
        }
    }

    public function create(array $data, $timeout = null)
    {
        $timeout = intval(is_null($timeout) ? $this->settings['timeout'] : $timeout);

        $payload = [
            'header' => [
                'expiration' => time() + $timeout,
            ],
            'body' => $data,
        ];

        return Jwt::encode($payload, $this->settings['secret'], $this->settings['algorithm']);
    }

    public function get($key)
    {
        try {
            $payload = Jwt::decode($key, $this->settings['secret'], true);
        }
        catch (\Exception $exception) {
            return false;
        }

        if (isset($payload['header']['expiration'])) {
            if ($payload['header']['expiration'] < time()) {
                return false;
            }
        }

        if (!isset($payload['body'])) {
            return false;
        }

        return $payload['body'];
    }
}