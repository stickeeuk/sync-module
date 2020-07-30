<?php

namespace Stickee\Sync;

use Exception;
use IteratorAggregate;

/**
 */
class JsonStreamIterator implements IteratorAggregate
{
    private $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function getIterator(): iterable
    {
        while (($line = fgets($this->stream)) !== false) {
            yield json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!feof($this->stream)) {
            throw new Exception('fgets error reading from stream');
        }
    }
}
