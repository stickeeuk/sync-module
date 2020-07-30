<?php

namespace Stickee\Sync;

use Exception;
use Illuminate\Support\Facades\DB;
use Stickee\Import\Importer;
use Stickee\Import\TableManagers\AutoTableManager;
use Stickee\Import\Utils\DataMerger;
use Stickee\Sync\JsonStreamIterator;
use Stickee\Sync\TableDescriber;
use Stickee\Sync\Traits\UsesTables;

/**
 */
class TableImporter
{
    use UsesTables;

    public function import($stream, string $configName): void
    {
        $config = $this->getTableInfo($configName);
        $connection = DB::connection($config['connection']);

        $tableDescriber = app(TableDescriber::class);
        $tableDescription = $tableDescriber->describe($configName);
        $columns = collect($tableDescription['columns'])
            ->pluck('name')
            ->all();

        $temporaryTableManager = app()->makeWith(
            AutoTableManager::class,
            [
                'db' => $connection,
                'tableName' => $config['table'],
                'importIndexes' => $config['importIndexes'],
            ]
        );

        $dataMerger = new DataMerger(
            $connection,
            $config['table'],
            $temporaryTableManager->getTableName(),
            $config['importIndexes'],
            $config['primary'],
            $columns,
            [],
            true,
            false
        );

        $iterable = app()->makeWith(JsonStreamIterator::class, ['stream' => $stream]);

        $importer = new Importer($dataMerger, $temporaryTableManager, $iterable);
        $importer->initialise();

        DB::transaction(function () use ($importer) {
            $importer->run();
        });
    }
}
