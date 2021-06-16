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
     * The renames to apply
     *
     * @param array $renames
     */
    private $renames = [];

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
     * Set the renames
     *
     * @param array $renames The renames to apply
     */
    public function setRenames($renames)
    {
        $this->renames = $renames;
    }

    /**
     * Get the iterator
     *
     * @return iterable
     */
    public function getIterator(): iterable
    {
        while (($line = fgets($this->stream)) !== false) {
            $lineData = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

            if ($this->renames) {
                foreach ($this->renames as $from => $to) {
                    $lineData[$to] = $lineData[$from];
                    unset($lineData[$from]);
                }
            }

            yield $lineData;
        }

        if (!feof($this->stream)) {
            throw new Exception('fgets error reading from stream');
        }
    }
}
