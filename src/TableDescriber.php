<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;

/**
 */
class TableDescriber implements TableDescriberInterface
{
    private $connection;

    public function __construct(?string $connection = null)
    {
        $this->connection = $connection ?: config('database.default');
    }

    public function describe(string $table): array
    {
        if (!in_array($table, config('sync.allowed_tables'))) {
            throw new InvalidArgumentException('Table "' . $table . '" is not in sync.allowed_tables');
        }

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
