<?php

namespace Stickee\Sync\Http\Controllers;

use Illuminate\Routing\Controller;
use Stickee\Sync\Http\Requests\SyncFileRequest;
use Stickee\Sync\Http\Requests\SyncTableRequest;
use Stickee\Sync\TableExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SyncController extends Controller
{
    public function getTable(SyncTableRequest $request)
    {
        $tableExporter = app(TableExporter::class);

        return new StreamedResponse(
            function () use ($request, $tableExporter) {
                $stream = fopen('php://output', 'w');
                $tableExporter->export($stream, $request->table);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $request->table . '.txt"',
            ]);
    }

    public function getFiles(SyncFileRequest $request)
    {

    }
}
