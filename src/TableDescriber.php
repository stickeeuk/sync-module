<?php

namespace Stickee\Sync;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;

/**
 */
class TableDescriber implements TableDescriberInterface
{
    public function __construct()
    {

    }

    public function describe(string $table, ?string $connection = null): array
    {
        if (!in_array($table, config('sync.allowed_tables'))) {
            throw new InvalidArgumentException('Table "' . $table . '" is not in sync.allowed_tables');
        }

        $schema = DB::connection($connection)->getSchemaBuilder();

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
}
