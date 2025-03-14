<?php

namespace Stickee\Sync;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Client
{
    /**
     * The number of files to get per HTTP request
     */
    public int $filesPerRequest;

    /**
     * Constructor
     *
     * @param \GuzzleHttp\Client $client The HTTP client
     * @param \Stickee\Sync\SyncService $syncService The sync service
     */
    public function __construct(
        protected GuzzleClient $client,
        protected SyncService $syncService
    ) {
        $this->filesPerRequest = Helpers::clientConfig('files_per_request');
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
        collect(Helpers::clientConfig('tables'))
            ->groupBy('connection', true)
            ->each(function (Collection $tables, string $connectionName): void {
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

        $singleTransaction = Helpers::clientConfig('single_transaction');
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
        } catch (Exception $exception) {
            /** @phpstan-ignore if.alwaysTrue */
            if ($singleTransaction) {
                $connection->rollback();
            }

            throw $exception;
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
        $directories = array_keys(Helpers::clientConfig('directories'));

        foreach ($directories as $directory) {
            $this->updateDirectory($directory);
        }
    }

    /**
     * Synchronise a single table
     *
     * @param string $configName The key in config('sync-client.tables')
     * @param \Stickee\Sync\TableImporter $importer The table importer
     */
    protected function updateTable(string $configName, TableImporter $importer): void
    {
        $response = $this->client->post(
            Helpers::clientConfig('api_url') . '/getTable',
            [
                'form_params' => [
                    'config_name' => $configName,
                    'hash' => $this->syncService->getTableHash(Helpers::CLIENT_CONFIG, $configName),
                ],
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            return;
        }

        // TODO: make this better
        $f = fopen('php://memory', 'w+b');
        fwrite($f, gzdecode((string) $response->getBody()));
        fseek($f, 0);

        $importer->import($f);
    }

    /**
     * Synchronise a single directory
     *
     * @param string $configName The key in config('sync-client.directories')
     */
    protected function updateDirectory(string $configName): void
    {
        $localHashes = $this->syncService->getFileHashes(Helpers::CLIENT_CONFIG, $configName);
        $remoteHashes = $this->getRemoteFileHashes($configName);

        // Delete any files that have been deleted on the server
        $this->syncService->deleteRemovedFiles($configName, $localHashes, $remoteHashes);

        // Remove unchanged files from the list to download
        $fileList = $remoteHashes->reject(fn($hash, $file): bool => ($hash !== '') && ($hash === ($localHashes[$file] ?? null)));

        foreach ($fileList->chunk($this->filesPerRequest) as $chunk) {
            $this->updateFilesChunk($configName, $chunk->keys()->all());
        }
    }

    /**
     * Get file hashes from the server
     *
     * @param string $configName The key in config('sync-client.directories')
     *
     * @return \Illuminate\Support\Collection A map of path to hash
     */
    protected function getRemoteFileHashes(string $configName): Collection
    {
        $response = $this->client->post(
            Helpers::clientConfig('api_url') . '/getFileHashes',
            [
                'form_params' => [
                    'config_name' => $configName,
                ],
            ]
        );

        $data = json_decode((string) $response->getBody(), true);

        return collect($data['hashes']);
    }

    /**
     * Update some files
     *
     * @param string $configName The key in config('sync-client.directories')
     * @param array $files The files to update
     */
    protected function updateFilesChunk(string $configName, array $files): void
    {
        $response = $this->client->post(
            Helpers::clientConfig('api_url') . '/getFiles',
            [
                'form_params' => [
                    'config_name' => $configName,
                    'files' => $files,
                ],
            ]
        );

        $importer = app(FileImporter::class);

        // TODO: make this better
        $f = fopen('php://memory', 'w+b');
        fwrite($f, (string) $response->getBody());
        fseek($f, 0);

        $importer->importToDirectory($f, $configName);
    }
}
