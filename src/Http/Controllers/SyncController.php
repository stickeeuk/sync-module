<?php

namespace Stickee\Sync\Http\Controllers;

use Illuminate\Routing\Controller;
use Stickee\Sync\Http\Requests\SyncRequest;
use Stickee\Sync\TableExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SyncController extends Controller
{
    public function getTable(SyncRequest $request)
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
}
