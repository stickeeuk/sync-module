<?php

namespace Stickee\Sync;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;

/**
 */
class Client
{
    /**
     * The HTTP client
     *
     * @var \GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * The sync service
     *
     * @var \Stickee\Sync\SyncService $syncService
     */
    protected $syncService;

    /**
     * The number of files to get per HTTP request
     *
     * @var int $filesPerRequest
     */
    public $filesPerRequest;

    /**
     * Constructor
     *
     * @param \GuzzleHttp\Client $client The HTTP client
     * @param \Stickee\Sync\SyncService $syncService The sync service
     */
    public function __construct(GuzzleClient $client, SyncService $syncService)
    {
        $this->client = $client;
        $this->syncService = $syncService;
        $this->filesPerRequest = config('sync.files_per_request');
    }

    /**
     * Synchronise files and tables
     */
    public function sync(): void
    {
        $this->updateFiles();
        $this->updateTables();
    }

    /**
     * Synchronise all tables
     */
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

    /**
     * Synchronise all files
     */
    protected function updateFiles(): void
    {
        $directories = array_keys(config('sync.directories'));

        foreach ($directories as $directory) {
            $this->updateDirectory($directory);
        }
    }

    /**
     * Synchronise a single table
     *
     * @param string $configName The name in config('sync.tables')
     * @param \Stickee\Sync\TableImporter The table importer
     */
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

    /**
     * Synchronise a single directory
     *
     * @param string $configName The name in config('sync.directories')
     */
    protected function updateDirectory(string $configName): void
    {
        $localHashes = $this->syncService->getFileHashes($configName);
        $remoteHashes = $this->getRemoteFileHashes($configName);

        // Delete any files that have been deleted on the server
        $this->syncService->deleteRemovedFiles($configName, $localHashes, $remoteHashes);

        // Remove unchanged files from the list to download
        $fileList = $remoteHashes->reject(function ($hash, $file) use ($localHashes) {
            return ($hash !== '') && ($hash === ($localHashes[$file] ?? null));
        });

        foreach ($fileList->chunk($this->filesPerRequest) as $chunk) {
            $this->updateFilesChunk($configName, $chunk->keys()->all());
        }
    }

    /**
     * Get file hashses from the server
     *
     * @param string $configName The name in config('sync.directories')
     *
     * @return \Illuminate\Support\Collection A map of path to hash
     */
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

    /**
     * Update some files
     *
     * @param string $configName The name in config('sync.directories')
     * @param array $files The files to update
     */
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
