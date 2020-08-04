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
     * @param string $configName The key in config('sync.tables')
     *
     * @return string
     */
    public function getTableHash(string $configName): string
    {
        $config = $this->getTableInfo($configName);

        $tableHasher = app()->makeWith(
            TableHasherInterface::class,
            ['connection' => $config['connection']]
        );

        return $tableHasher->hash($configName);
    }

    /**
     * Export a table to a stream
     *
     * @param string $configName The key in config('sync.tables')
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
     * @param string $configName The key in config('sync.directories')
     *
     * @return \Illuminate\Support\Collection A map of file => hash
     */
    public function getFileHashes(string $configName): Collection
    {
        $config = $this->getDirectoryInfo($configName);

        $directoryHasher = app($config['hasher']);

        return collect($directoryHasher->hash($configName));
    }

    /**
     * Export files to a stream
     *
     * @param string $configName The key in config('sync.directories')
     * @param array $files The files to export
     * @param mixed $stream The stream to write to
     */
    public function exportFiles(string $configName, array $files, $stream): void
    {
        $config = $this->getDirectoryInfo($configName);
        $fileExporter = app(FileExporter::class);
        $fileExporter->export($stream, $configName, $files);
    }

    /**
     * Delete files that no longer exist on the server
     *
     * @param string $configName The key in config('sync.directories')
     * @param \Illuminate\Support\Collection $localHashes A map of file => hash on the client
     * @param \Illuminate\Support\Collection $remoteHashes A map of file => hash on the server
     */
    public function deleteRemovedFiles(string $configName, Collection $localHashes, Collection $remoteHashes): void
    {
        $config = $this->getDirectoryInfo($configName);
        $disk = Storage::disk($config['disk']);

        foreach ($localHashes as $file => $hash) {
            if (!isset($remoteHashes[$file])) {
                $disk->delete($file);
            }
        }
    }
}
