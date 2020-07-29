<?php

namespace Stickee\Sync;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Stickee\Sync\Models\Property;

/**
 */
class Client implements ClientInterface
{
    protected $client;
    public $filesPerRequest = 10;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function sync(): void
    {
        $this->updateFiles();
        $this->updateTables();
    }

    public function updateTables(): void
    {
        foreach ($tables as $tableName => $tableConfig) {
            $localHash = app()->makeWith(TableHasherInterface::class, $tableConfig)->hash($tableName);
            $remoteHash = $this->getRemoteHash($table);

            if ($localHash !== $remoteHash) {
                $this->updateTable($tableName, $tableConfig);
            }
        }
    }

    public function updateFiles(): void
    {
        foreach ($directories as $directory) {
            $localHashes = [];// tODO
            $remoteHashes = collect([]); // todo

            // Remove any files that have been deleted
            foreach ($localHashes as $file => $hash) {
                if (!isset($remoteHashes[$file])) {
                    // TODO
                    // Storage::disk($disk)->delete($path);
                    dump('Delete ' . $file);
                }
            }

            // Remove unchanged files
            $fileList = $remoteHashes->reject(function ($hash, $file) use ($localHashes) {
                return ($hash !== '') && ($hash === ($localHashes[$file] ?? null));
            });

            foreach ($fileList->chunk($this->filesPerRequest) as $chunk) {
                $this->updateFilesChunk($chunk->keys());
            }
        }
    }

    public function updateTable($name, $config): void
    {
        $response = $this->client->post(config('sync.api_url'), ['table' => $name]);
        // todo merge in to table
        $connection = $config['connection'] ?? config('database.default');
        $dest = DB::connection($connection);
    }

    public function updateFilesChunk($files)
    {
        $response = $this->client->post()
    }
}
