<?php

namespace Stickee\Sync\Interfaces;

interface TableDescriberInterface
{
    function describe(string $table): array;
    function getConnection(): ?string;
}
