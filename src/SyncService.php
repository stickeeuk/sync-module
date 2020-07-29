<?php

namespace Stickee\Sync;

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

    public function getFileHashes(string $configName)
    {
        $config = $this->getDirectoryInfo($configName);

        $directoryHasher = app($config['hasher']);

        return $directoryHasher->hash($configName);
    }

    public function exportFiles(string $configName, array $files, $stream)
    {
        $config = $this->getDirectoryInfo($configName);
        $fileExporter = app(FileExporter::class);
        $fileExporter->export($stream, $configName, $files);
    }
}
