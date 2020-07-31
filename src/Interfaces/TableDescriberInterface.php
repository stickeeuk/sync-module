<?php

namespace Stickee\Sync\Interfaces;

interface TableDescriberInterface
{
    /**
     * Get a description of a table
     *
     * @param string $table The table name
     *
     * @return array
     */
    function describe(string $table): array;
}
