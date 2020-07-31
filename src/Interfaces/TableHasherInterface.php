<?php

namespace Stickee\Sync\Interfaces;

interface TableHasherInterface
{
    /**
     * Get a hash of the data in a table
     *
     * @param string $configName The key in config('sync.tables')
     *
     * @return string
     */
    function hash(string $configName): string;
}
