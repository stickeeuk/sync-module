<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\DB;
use Stickee\Import\Importer;
use Stickee\Import\TableManagers\AutoTableManager;
use Stickee\Import\Utils\DataMerger;
use Stickee\Sync\Helpers;
use Stickee\Sync\JsonStreamIterator;
use Stickee\Sync\TableDescriber;
use Stickee\Sync\Traits\UsesTables;

/**
 */
class TableImporter
{
    use UsesTables;

    /**
     * The key in config('sync-client.tables')
     *
     * @var string $configName
     */
    private $configName;

    /**
     * The importer
     *
     * @var ?\Stickee\Import\Importer $importer
     */
    private $importer;

    /**
     * The iterable supplying the data
     *
     * @var ?\Stickee\Sync\JsonStreamIterator $iterable
     */
    private $iterable;

    /**
     * Constructor
     *
     * @param string $configName The key in config('sync-client.tables')
     */
    public function __construct(string $configName)
    {
        $this->configName = $configName;
    }

    /**
     * Initialise the importer
     */
    public function initialise(): void
    {
        $config = $this->getTableInfo(Helpers::CLIENT_CONFIG, $this->configName);
        $connection = DB::connection($config['connection']);

        $tableDescriber = app(TableDescriber::class);
        $tableDescription = $tableDescriber->describe(Helpers::CLIENT_CONFIG, $this->configName);
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

        $dataMerger = app()
            ->makeWith(
                DataMerger::class,
                [
                    'db' => $connection,
                    'tableName' => $config['table'],
                    'tempTableName' => $temporaryTableManager->getTableName(),
                    'columns' => $columns,
                ]
            )
            ->setImportIndexes($config['importIndexes'])
            ->setJoinFields($config['primary'])
            ->setAddAutoIdColumn(false);

        $this->iterable = app(JsonStreamIterator::class);
        $this->iterable->setRenames($config['config']['renames'] ?? []);

        $this->importer = new Importer($dataMerger, $temporaryTableManager, $this->iterable);
        $this->importer->initialise();
    }

    /**
     * Import a stream to the table
     *
     * @param mixed $stream The stream to read from
     */
    public function import($stream): void
    {
        if (!$this->importer) {
            $this->initialise();
        }

        $this->iterable->setStream($stream);

        $config = $this->getTableInfo(Helpers::CLIENT_CONFIG, $this->configName);
        $connection = DB::connection($config['connection']);

        $connection->transaction(function () {
            $this->importer->run();
        });
    }
}
