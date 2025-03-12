<?php

namespace Stickee\Sync;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\Traits\UsesDirectories;
use Stickee\Sync\Traits\UsesTables;

/**
 * Sync Service
 */
class SyncService
{
    use UsesDirectories;
    use UsesTables;

    /**
     * Get the hash of a table
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key in config('sync-client.tables') or config('sync-server.tables')
     */
    public function getTableHash(string $configType, string $configName): string
    {
        $config = $this->getTableInfo($configType, $configName);

        $tableHasher = app()->makeWith(
            TableHasherInterface::class,
            ['connection' => $config['connection']]
        );

        return $tableHasher->hash($configType, $configName);
    }

    /**
     * Export a table to a stream
     *
     * @param string $configName The key in config('sync-client.tables') or config('sync-server.tables')
     * @param mixed $stream The stream to write to
     */
    public function exportTable(string $configName, $stream): void
    {
        $tableExporter = app(TableExporter::class);
        $tableExporter->export($stream, $configName);
    }

    /**
     * Get file hashes
     *
     * @param string $configType The config type - 'sync-client' or 'sync-server'
     * @param string $configName The key in config('sync-client.directories') or config('sync-server.directories')
     *
     * @return \Illuminate\Support\Collection A map of file => hash
     */
    public function getFileHashes(string $configType, string $configName): Collection
    {
        $config = $this->getDirectoryInfo($configType, $configName);

        $directoryHasher = app($config['hasher']);

        return collect($directoryHasher->hash($configType, $configName));
    }

    /**
     * Export files to a stream
     *
     * @param string $configName The key in config('sync-client.directories') or config('sync-server.directories')
     * @param array $files The files to export
     * @param mixed $stream The stream to write to
     */
    public function exportFiles(string $configName, array $files, $stream): void
    {
        $fileExporter = app(FileExporter::class);
        $fileExporter->export($stream, $configName, $files);
    }

    /**
     * Delete files that no longer exist on the server
     *
     * @param string $configName The key in config('sync-client.directories') or config('sync-server.directories')
     * @param \Illuminate\Support\Collection $localHashes A map of file => hash on the client
     * @param \Illuminate\Support\Collection $remoteHashes A map of file => hash on the server
     */
    public function deleteRemovedFiles(string $configName, Collection $localHashes, Collection $remoteHashes): void
    {
        $config = $this->getDirectoryInfo(Helpers::CLIENT_CONFIG, $configName);
        $disk = Storage::disk($config['disk']);

        foreach ($localHashes as $file => $hash) {
            if (!isset($remoteHashes[$file])) {
                $disk->delete($file);
            }
        }
    }
}
