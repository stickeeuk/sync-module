<?php

namespace Stickee\Sync\Interfaces;

interface TableHasherInterface
{
    function hash(string $table): string;
}
