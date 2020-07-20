<?php

namespace Stickee\Sync\TableDescribers;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stickee\Sync\Interfaces\TableDescriberInterface;

/**
 */
class MySqlTableDescriber implements TableDescriberInterface
{
    public function __construct()
    {

    }

    public function describe(string $table, ?string $connection = null): array
    {
        if (!in_array($table, config('sync.allowed_tables'))) {
            throw new InvalidArgumentException('Table "' . $table . '" is not in sync.allowed_tables');
        }

        $result = ['columns' => []];
        $rows = DB::select('DESCRIBE ' . DB::getTablePrefix() . $table);

        foreach ($rows as $row) {
            $result['columns'][] = [
                'name' => $row->Field,
                'type' => $row->Type, // TODO canonicalise?
                'nullable' => $row->Null === 'YES',
            ];
        }

        return $result;
    }
}
