<?php

namespace Stickee\Sync\Http\Controllers;

use Illuminate\Routing\Controller;
use Stickee\Sync\FileExporter;
use Stickee\Sync\Http\Requests\GetFileHashesRequest;
use Stickee\Sync\Http\Requests\GetFilesRequest;
use Stickee\Sync\Http\Requests\GetTableHashRequest;
use Stickee\Sync\Http\Requests\GetTableRequest;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\SyncService;
use Stickee\Sync\TableExporter;
use Stickee\Sync\Traits\UsesDirectories;
use Stickee\Sync\Traits\UsesTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SyncController extends Controller
{
    use UsesTables;
    use UsesDirectories;

    public function getTableHash(GetTableHashRequest $request, SyncService $syncService)
    {
        return [
            'hash' => $syncService->getTableHash($request->config_name),
        ];
    }

    public function getTable(GetTableRequest $request, SyncService $syncService)
    {
        if ($request->hash) {
            $hash = $syncService->getTableHash($request->config_name);

            abort_if($hash === $request->hash, 304);
        }

        $tableExporter = app(TableExporter::class);

        return new StreamedResponse(
            function () use ($request, $tableExporter) {
                $stream = fopen('php://output', 'w');
                $tableExporter->export($stream, $request->config_name);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $request->config_name . '.txt"',
            ]);
    }

    public function getFileHashes(GetFileHashesRequest $request, SyncService $syncService)
    {
        return [
            'hashes' => $syncService->getFileHashes($request->config_name),
        ];
    }

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
