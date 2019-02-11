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
        else {
            $this->settings['dir'] = rtrim($this->settings['dir'], '\\/');
        }
    }

    protected function buildFilename($key)
    {
        $key = $this->buildKey($key);

        return $this->settings['dir'] . '/' .  $key;
    }

    public function get($key)
    {
        @include $this->buildFilename($key);

        if (isset($expiration) AND $expiration < time()) {
            return false;
        }

        return isset($data) ? $data : false;
    }

    public function set($key, $data, $ttl = null)
    {
        $data = var_export($data, true);

        // HHVM fails at __set_state, so just use object cast for now
        $data = str_replace('stdClass::__set_state', '(object)', $data);
        $ttl = $ttl === null ? $this->settings['ttl'] : $ttl;

        // Write to temp file first to ensure atomicity
        $filename = $this->buildFilename($key);
        $tmpFilename = sprintf('%s.%s.tmp', $filename, uniqid('', true));
        if ($ttl === 0) {
            $content = sprintf('<?php $data = %s;', $data);
        }
        else {
            $content = sprintf('<?php $expiration = %d; $data = %s;', time() + $ttl, $data);
        }
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