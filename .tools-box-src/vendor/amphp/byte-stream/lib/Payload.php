<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
class Payload implements InputStream
{
    private $stream;
    private $promise;
    private $lastRead;
    public function __construct(InputStream $stream)
    {
        $this->stream = $stream;
    }
    public function __destruct()
    {
        if (!$this->promise) {
            Promise\rethrow(new Coroutine($this->consume()));
        }
    }
    private function consume() : \Generator
    {
        try {
            if ($this->lastRead && null === (yield $this->lastRead)) {
                return;
            }
            while (null !== (yield $this->stream->read())) {
            }
        } catch (\Throwable $exception) {
        }
    }
    public final function read() : Promise
    {
        if ($this->promise) {
            throw new \Error("Cannot stream message data once a buffered message has been requested");
        }
        return $this->lastRead = $this->stream->read();
    }
    public final function buffer() : Promise
    {
        if ($this->promise) {
            return $this->promise;
        }
        return $this->promise = call(function () {
            $buffer = '';
            if ($this->lastRead && null === (yield $this->lastRead)) {
                return $buffer;
            }
            while (null !== ($chunk = (yield $this->stream->read()))) {
                $buffer .= $chunk;
            }
            return $buffer;
        });
    }
}
