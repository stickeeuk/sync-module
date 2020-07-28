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

    public function describe(string $table): array
    {
        $this->checkTable($table);

        $config = config('sync.tables');
        $connection = $config['connection'] ?? config('database.default');

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
