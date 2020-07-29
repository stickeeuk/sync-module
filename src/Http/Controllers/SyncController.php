<?php

namespace Stickee\Sync\Http\Controllers;

use Illuminate\Routing\Controller;
use Stickee\Sync\Http\Requests\GetTableHashRequest;
use Stickee\Sync\Http\Requests\GetTableRequest;
use Stickee\Sync\Interfaces\TableHasherInterface;
use Stickee\Sync\TableExporter;
use Stickee\Sync\Traits\UsesTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SyncController extends Controller
{
    use UsesTables;

    public function getTableHash(GetTableHashRequest $request)
    {
        $config = $this->getTableInfo($request->config_name);

        $tableHasher = app()
            ->makeWith(TableHasherInterface::class, ['connection' => $config['connection']]);

        return [
            'hash' => $tableHasher->hash($config['table']),
        ];
    }

    public function getTable(GetTableRequest $request)
    {
        // TODO id the hash is specified, check it and return 304 if equal
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

    public function getFileHashes(GetFileHashesRequest $request)
    {

    }

    public function getFiles(GetFilesRequest $request)
    {

    }
}
