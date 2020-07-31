<?php

namespace Stickee\Sync\Http\Controllers;

use Illuminate\Routing\Controller;
use Stickee\Sync\Http\Requests\GetFileHashesRequest;
use Stickee\Sync\Http\Requests\GetFilesRequest;
use Stickee\Sync\Http\Requests\GetTableHashRequest;
use Stickee\Sync\Http\Requests\GetTableRequest;
use Stickee\Sync\SyncService;
use Stickee\Sync\Traits\UsesDirectories;
use Stickee\Sync\Traits\UsesTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SyncController extends Controller
{
    use UsesTables;
    use UsesDirectories;

    /**
     * Get a table hash
     *
     * @param \Stickee\Sync\Http\Requests\GetTableHashRequest $request The request
     * @param \Stickee\Sync\SyncService $syncService The sync service
     *
     * @return array
     */
    public function getTableHash(GetTableHashRequest $request, SyncService $syncService): array
    {
        return [
            'hash' => $syncService->getTableHash($request->config_name),
        ];
    }

    /**
     * Get a table
     *
     * @param \Stickee\Sync\Http\Requests\GetTableRequest $request The request
     * @param \Stickee\Sync\SyncService $syncService The sync service
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getTable(GetTableRequest $request, SyncService $syncService)
    {
        if ($request->hash) {
            $hash = $syncService->getTableHash($request->config_name);

            abort_if($hash === $request->hash, 304);
        }

        return new StreamedResponse(
            function () use ($request, $syncService) {
                $stream = fopen('php://output', 'w');
                $syncService->exportTable($request->config_name, $stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $request->config_name . '.txt"',
            ]);
    }

    /**
     * Get file hashes
     *
     * @param \Stickee\Sync\Http\Requests\GetFileHashesRequest $request The request
     * @param \Stickee\Sync\SyncService $syncService The sync service
     *
     * @return array
     */
    public function getFileHashes(GetFileHashesRequest $request, SyncService $syncService)
    {
        return [
            'hashes' => $syncService->getFileHashes($request->config_name),
        ];
    }

    /**
     * Get a table hash
     *
     * @param \Stickee\Sync\Http\Requests\GetFilesRequest $request The request
     * @param \Stickee\Sync\SyncService $syncService The sync service
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getFiles(GetFilesRequest $request, SyncService $syncService)
    {
        return new StreamedResponse(
            function () use ($request, $syncService) {
                $stream = fopen('php://output', 'w');
                $syncService->exportFiles($request->config_name, $request->input('files'), $stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $request->config_name . '.bin"',
            ]);
    }
}
