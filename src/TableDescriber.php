<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;
use Stickee\Sync\Traits\ChecksTables;

/**
 */
class TableDescriber implements TableDescriberInterface
{
    use ChecksTables;

    private $connection;

    public function __construct(?string $connection = null)
    {
        $this->connection = $connection ?: config('database.default');
    }

    public function describe(string $table): array
    {
        $this->checkTable($table);

        $schema = DB::connection($this->connection)->getSchemaBuilder();

        $columns = $schema->getColumnListing($table);
        $result = ['columns' => []];

        foreach ($columns as $column) {
            $type = $schema->getColumnType($table, $column);

            $result['columns'][] = [
                'name' => $column,
                'type' => $type,
            ];
        }

        return $result;
    }

    public function getConnection(): ?string
    {
        return $this->connection;
    }
}
