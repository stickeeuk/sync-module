<?php

namespace Stickee\Sync\Interfaces;

interface TableDescriberInterface
{
    /**
     * Get a description of a table
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key from config('sync-client.tables') or config('sync-server.tables')
     */
    public function describe(string $configType, string $configName): array;
}
