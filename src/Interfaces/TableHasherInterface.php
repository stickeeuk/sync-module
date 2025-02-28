<?php

namespace Stickee\Sync\Interfaces;

interface TableHasherInterface
{
    /**
     * Get a hash of the data in a table
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key from config('sync-client.tables') or config('sync-server.tables')
     *
     * @return string
     */
    public function hash(string $configType, string $configName): string;
}
