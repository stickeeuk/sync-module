<?php

namespace Stickee\Sync;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stickee\Sync\Models\Property;
use Stickee\Sync\TableImporter;

/**
 */
class Client
{
    protected $client;
    protected $syncService;
    public $filesPerRequest;

    public function __construct(GuzzleClient $client, SyncService $syncService)
    {
        $this->client = $client;
        $this->syncService = $syncService;
        $this->filesPerRequest = config('sync.files_per_request');
    }

    public function sync(): void
    {
        $this->updateFiles();
        $this->updateTables();
    }

    protected function updateTables(): void
    {
        $tables = array_keys(config('sync.tables'));
        $importers = [];
        $app = app();

        // TableImporter::initialise() uses DDL which will close any open transactions
        // in MySQL, so initialise them all first
        foreach ($tables as $configName) {
            $importer = $app->makeWith(TableImporter::class, ['configName' => $configName]);
            $importer->initialise();

            $importers[$configName] = $importer;
        }

        if (config('sync.single_transaction')) {
            DB::startTransaction();
        }

        foreach ($tables as $configName) {
            $this->updateTable($configName, $importers[$configName]);
        }

        if (config('sync.single_transaction')) {
            DB::commit();
        }
    }

    protected function updateFiles(): void
    {
        $directories = array_keys(config('sync.directories'));

        foreach ($directories as $directory) {
            $this->updateDirectory($directory);
        }
    }

    protected function updateTable(string $configName, TableImporter $importer): void
    {
        $response = $this->client->post(
            config('sync.api_url') . '/getTable',
            [
                'form_params' => [
                    'config_name' => $configName,
                    'hash' => $this->syncService->getTableHash($configName),
                ],
            ]
        );

        // Not modified
        if ($response->getStatusCode() === 304) {
            return;
        }

        // TODO: make this better
        $f = fopen('php://memory', 'w+');
        fwrite($f, gzdecode((string)$response->getBody()));
        fseek($f, 0);

        $importer->import($f);
    }

    protected function updateDirectory(string $configName): void
    {
        $localHashes = $this->syncService->getFileHashes($configName);
        $remoteHashes = $this->getRemoteFileHashes($configName);

        // Remove any files that have been deleted on the server
        $this->syncService->deleteRemovedFiles($configName, $localHashes, $remoteHashes);

        // Remove unchanged files
        $fileList = $remoteHashes->reject(function ($hash, $file) use ($localHashes) {
            return ($hash !== '') && ($hash === ($localHashes[$file] ?? null));
        });

        foreach ($fileList->chunk($this->filesPerRequest) as $chunk) {
            $this->updateFilesChunk($configName, $chunk->keys()->all());
        }
    }

    protected function getRemoteFileHashes(string $configName): Collection
    {
        $response = $this->client->post(
            config('sync.api_url') . '/getFileHashes',
            [
                'form_params' => [
                    'config_name' => $configName,
                ],
            ]
        );

        $data = json_decode((string)$response->getBody(), true);

        return collect($data['hashes']);
    }

    protected function updateFilesChunk(string $configName, array $files): void
    {
        $response = $this->client->post(
            config('sync.api_url') . '/getFiles',
            [
                'form_params' => [
                    'config_name' => $configName,
                    'files' => $files,
                ],
            ]
        );

        $importer = app(FileImporter::class);

        // TODO: make this better
        $f = fopen('php://memory', 'w+');
        fwrite($f, (string)$response->getBody());
        fseek($f, 0);
        $importer->importToDirectory($f, $configName);
    }
}
