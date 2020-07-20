<?php

namespace Stickee\Sync\Interfaces;

interface TableDescriberInterface
{
    function describe(string $table, ?string $connection = null): array;
}
