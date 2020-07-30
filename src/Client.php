<?php

namespace Stickee\Sync;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stickee\Sync\Models\Property;

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

        foreach ($tables as $configName) {
            $this->updateTable($configName);
        }
    }

    protected function updateFiles(): void
    {
        $directories = array_keys(config('sync.directories'));

        foreach ($directories as $directory) {
            $this->updateDirectory($directory);
        }
    }

    protected function updateTable($configName): void
    {
        dump($configName);
        $data = ['config_name' => $configName];

        // TODO $hash = $this->syncService->getTableHash($configName);
        $hash = $this->syncService->getTableHash('users_dest');

        if ($hash !== '') {
            $data['hash'] = $hash;
        }

        dump( $this->syncService->getTableHash($configName));
        $response = $this->client->post(
            config('sync.api_url') . '/getTable',
            ['form_params' => $data]
        );

        // Not modified
        if ($response->getStatusCode() === 304) {
            return;
        }

        dump(gzdecode((string)$response->getBody()));

        // todo merge in to table
        // $config =
        // $connection = $config['connection'] ?? config('database.default');
        // $dest = DB::connection($connection);
    }

    protected function updateDirectory(string $configName): void
    {
        // TODO $localHashes = $this->syncService->getFileHashes($configName);
        $localHashes = $this->syncService->getFileHashes('dest');
        $remoteHashes = $this->getRemoteFileHashes($configName);

        // Remove any files that have been deleted
        foreach ($localHashes as $file => $hash) {
            if (!isset($remoteHashes[$file])) {
                // TODO
                $disk = 'dest';
                Storage::disk($disk)->delete($file);
            }
        }

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

        // todo $importer->importToDirectory($response->getBody(), $configName);
        // todo make this better
        $f = fopen('php://memory', 'w+');
        fwrite($f, (string)$response->getBody());
        fseek($f, 0);
        $importer->importToDirectory($f, 'dest'); // todo $configName
    }
}
