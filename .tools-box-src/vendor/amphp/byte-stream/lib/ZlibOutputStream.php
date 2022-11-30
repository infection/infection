<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Promise;
final class ZlibOutputStream implements OutputStream
{
    private $destination;
    private $encoding;
    private $options;
    private $resource;
    public function __construct(OutputStream $destination, int $encoding, array $options = [])
    {
        $this->destination = $destination;
        $this->encoding = $encoding;
        $this->options = $options;
        $this->resource = @\deflate_init($encoding, $options);
        if ($this->resource === \false) {
            throw new StreamException("Failed initializing deflate context");
        }
    }
    public function write(string $data) : Promise
    {
        if ($this->resource === null) {
            throw new ClosedException("The stream has already been closed");
        }
        \assert($this->destination !== null);
        $compressed = \deflate_add($this->resource, $data, \ZLIB_SYNC_FLUSH);
        if ($compressed === \false) {
            throw new StreamException("Failed adding data to deflate context");
        }
        $promise = $this->destination->write($compressed);
        $promise->onResolve(function ($error) {
            if ($error) {
                $this->close();
            }
        });
        return $promise;
    }
    public function end(string $finalData = "") : Promise
    {
        if ($this->resource === null) {
            throw new ClosedException("The stream has already been closed");
        }
        \assert($this->destination !== null);
        $compressed = \deflate_add($this->resource, $finalData, \ZLIB_FINISH);
        if ($compressed === \false) {
            throw new StreamException("Failed adding data to deflate context");
        }
        $promise = $this->destination->end($compressed);
        $promise->onResolve(function () {
            $this->close();
        });
        return $promise;
    }
    private function close()
    {
        $this->resource = null;
        $this->destination = null;
    }
    public function getEncoding() : int
    {
        return $this->encoding;
    }
    public function getOptions() : array
    {
        return $this->options;
    }
}
