<?php

namespace Stickee\Sync\Interfaces;

interface DirectoryHasherInterface
{
    /**
     * Hash a directory specified in config('sync.directories')
     *
     * @param string $configName The key from config('sync.directories')
     *
     * @return array A map of file => hash
     */
    function hash(string $configName): array;
}
