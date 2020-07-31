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

    public function getTableHash(string $configName)
    {
        $config = $this->getTableInfo($configName);

        $tableHasher = app()->makeWith(
            TableHasherInterface::class,
            ['connection' => $config['connection']]
        );

        return $tableHasher->hash($configName);
    }

    public function exportTable(string $configName, $stream)
    {
        $tableExporter = app(TableExporter::class);
        $tableExporter->export($stream, $configName);
    }

    public function getFileHashes(string $configName): Collection
    {
        $config = $this->getDirectoryInfo($configName);

        $directoryHasher = app($config['hasher']);

        return collect($directoryHasher->hash($configName));
    }

    public function exportFiles(string $configName, array $files, $stream)
    {
        $config = $this->getDirectoryInfo($configName);
        $fileExporter = app(FileExporter::class);
        $fileExporter->export($stream, $configName, $files);
    }

    public function deleteRemovedFiles(string $configName, Collection $localHashes, Collection $remoteHashes)
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
