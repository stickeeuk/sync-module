<?php

namespace Stickee\Sync;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $this->filesPerRequest = config('sync.client.files_per_request');
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
        collect(config('sync.tables'))
            ->groupBy('connection', true)
            ->each(function (Collection $tables, string $connectionName) {
                // TableImporter::initialise() uses DDL which will close any open transactions
                // in MySQL, so initialise them all first
                $importers = $tables->map(function ($config, $configName) {
                    $importer = app()->makeWith(TableImporter::class, ['configName' => $configName]);
                    $importer->initialise();

                    return $importer;
                });

                $this->updateConnectionTables($connectionName, $importers);
            });
    }

    /**
     * Update all the tables for a single database connection
     *
     * @param string $connectionName The connection name
     * @param \Illuminate\Support\Collection $importers The table importers
     */
    protected function updateConnectionTables(string $connectionName, Collection $importers): void
    {
        if ($importers->isEmpty()) {
            return;
        }

        $singleTransaction = config('sync.client.single_transaction');
        $connection = DB::connection($connectionName);

        // Disable foreign key checks so we don't have to do the tables in any particular order
        // Disable unique checks for speed (doesn't affect inserting duplicates on InnoDB)
        $connection->statement('SET FOREIGN_KEY_CHECKS = 0');
        $connection->statement('SET UNIQUE_CHECKS = 0');

        if ($singleTransaction) {
            $connection->beginTransaction();
        }

        try {
            foreach ($importers as $configName => $importer) {
                $this->updateTable($configName, $importer);
            }

            if ($singleTransaction) {
                $connection->commit();
            }
        } catch (Exception $e) {
            if ($singleTransaction) {
                $connection->rollback();
            }

            throw $e;
        } finally {
            $connection->statement('SET FOREIGN_KEY_CHECKS = 1');
            $connection->statement('SET UNIQUE_CHECKS = 1');
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
            config('sync.client.api_url') . '/getTable',
            [
                'form_params' => [
                    'config_name' => $configName,
                    'hash' => $this->syncService->getTableHash($configName),
                ],
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
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
     * Get file hashes from the server
     *
     * @param string $configName The name in config('sync.directories')
     *
     * @return \Illuminate\Support\Collection A map of path to hash
     */
    protected function getRemoteFileHashes(string $configName): Collection
    {
        $response = $this->client->post(
            config('sync.client.api_url') . '/getFileHashes',
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
            config('sync.client.api_url') . '/getFiles',
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
