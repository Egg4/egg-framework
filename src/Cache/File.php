<?php

namespace Egg\Cache;

class File extends AbstractCache
{
    public function __construct(array $settings = [])
    {
        parent::__construct(array_merge([
            'dir'           => null,
            'ttl'           => 3600,
        ], $settings));

        if (is_null($this->settings['dir'])) {
            throw new \Exception('Cache file dir not set');
        }
        $this->settings['dir'] = rtrim($this->settings['dir'], '\\/');
        if (!file_exists($this->settings['dir'])) {
            mkdir($this->settings['dir'], 0777, true);
        }
    }

    protected function buildFilename($key)
    {
        $key = $this->buildKey($key);

        return $this->settings['dir'] . '/' .  $key;
    }

    public function get($key)
    {
        $filename = $this->buildFilename($key);
        $content = @file_get_contents($filename);
        if (!$content) {
            return false;
        }
        $payload = unserialize($content);
        if (isset($payload['header']['expiration'])) {
            if ($payload['header']['expiration'] < time()) {
                return false;
            }
        }

        return isset($payload['body']) ? $payload['body'] : false;
    }

    public function set($key, $data, $ttl = null)
    {
        $payload = [
            'header'    => [],
            'body'      => $data,
        ];

        $ttl = intval(is_null($ttl) ? $this->settings['ttl'] : $ttl);
        if ($ttl > 0) {
            $payload['header']['expiration'] = time() + $ttl;
        }

        $filename = $this->buildFilename($key);
        $tmpFilename = sprintf('%s.%s.tmp', $filename, uniqid('', true));
        $content = serialize($payload);
        file_put_contents($tmpFilename, $content, LOCK_EX);
        rename($tmpFilename, $filename);
    }

    public function defer($key, $ttl = null)
    {
        $data = $this->get($key);
        if (!$data) {
            throw new \Exception(sprintf('Cache key "%s" not found', $key));
        }
        $this->set($key, $data, $ttl);
    }

    public function delete($key)
    {
        $filename = $this->buildFilename($key);
        @unlink($filename);
    }

    public function clear()
    {
        if (!empty($this->settings['namespace'])) {
            $pattern = sprintf('%s/%s.*', $this->settings['dir'], $this->settings['namespace']);
            foreach (glob($pattern) as $filename) {
                @unlink($filename);
            }
        }
    }
}