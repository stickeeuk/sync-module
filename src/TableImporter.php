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

    private $configName;
    private $importer;

    public function __construct(string $configName)
    {
        $this->configName = $configName;
    }

    public function initialise(): void
    {
        $config = $this->getTableInfo($this->configName);
        $connection = DB::connection($config['connection']);

        $tableDescriber = app(TableDescriber::class);
        $tableDescription = $tableDescriber->describe($this->configName);
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

        $this->importer = new Importer($dataMerger, $temporaryTableManager, $iterable);
        $this->importer->initialise();
    }

    public function import($stream): void
    {
        if (!$this->importer) {
            $this->initialise();
        }

        DB::transaction(function () use ($importer) {
            $importer->run();
        });
    }
}
