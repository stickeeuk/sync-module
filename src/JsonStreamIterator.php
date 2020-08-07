<?php

namespace Stickee\Sync;

use Exception;
use IteratorAggregate;

/**
 * Iterate over a stream that contains JSON objects separated by \n
 */
class JsonStreamIterator implements IteratorAggregate
{
    /**
     * The stream to iterate
     *
     * @param mixed $stream
     */
    private $stream;

    /**
     * Constructor
     *
     * @param mixed $stream The stream to iterate
     */
    public function __construct($stream = null)
    {
        $this->stream = $stream;
    }

    /**
     * Set the stream
     *
     * @param mixed $stream The stream to iterate
     */
    public function setStream($stream)
    {
        $this->stream = $stream;
    }

    /**
     * Get the iterator
     *
     * @return iterable
     */
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
