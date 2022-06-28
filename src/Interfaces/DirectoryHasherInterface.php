<?php

namespace Stickee\Sync\Interfaces;

interface DirectoryHasherInterface
{
    /**
     * Hash a directory specified in config('sync-client.directories') or config('sync-server.directories')
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key from config('sync-client.directories') or config('sync-server.directories')
     *
     * @return array A map of file => hash
     */
    function hash(string $configType, string $configName): array;
}
