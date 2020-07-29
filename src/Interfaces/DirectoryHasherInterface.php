<?php

namespace Stickee\Sync\Interfaces;

interface DirectoryHasherInterface
{
    function hash(string $directory): array;
}
