<?php

namespace Stickee\Sync;

class Helpers
{
    /**
     * Prefix for server-side config
     *
     * @var string SERVER_CONFIG
     */
    const SERVER_CONFIG = 'sync-server';

    /**
     * Prefix for client-side config
     *
     * @var string CLIENT_CONFIG
     */
    const CLIENT_CONFIG = 'sync-client';

    /**
     * Get client configuration
     *
     * @param string $name The config name
     *
     * @return mixed
     */
    public static function clientConfig(string $name = '')
    {
        return config(self::CLIENT_CONFIG . ($name === '' ? '' : '.' . $name));
    }

    /**
     * Get server configuration
     *
     * @param string $name The config name
     *
     * @return mixed
     */
    public static function serverConfig(string $name = '')
    {
        return config(self::SERVER_CONFIG . ($name === '' ? '' : '.' . $name));
    }
}
