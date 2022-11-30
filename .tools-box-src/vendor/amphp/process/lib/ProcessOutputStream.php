<?php

namespace _HumbugBoxb47773b41c19\Amp\Process;

use _HumbugBoxb47773b41c19\Amp\ByteStream\ClosedException;
use _HumbugBoxb47773b41c19\Amp\ByteStream\OutputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\ResourceOutputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\StreamException;
use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Promise;
final class ProcessOutputStream implements OutputStream
{
    private $queuedWrites;
    private $shouldClose = \false;
    private $resourceStream;
    private $error;
    public function __construct(Promise $resourceStreamPromise)
    {
        $this->queuedWrites = new \SplQueue();
        $resourceStreamPromise->onResolve(function ($error, $resourceStream) {
            if ($error) {
                $this->error = new StreamException("Failed to launch process", 0, $error);
                while (!$this->queuedWrites->isEmpty()) {
                    list(, $deferred) = $this->queuedWrites->shift();
                    $deferred->fail($this->error);
                }
                return;
            }
            while (!$this->queuedWrites->isEmpty()) {
                list($data, $deferred) = $this->queuedWrites->shift();
                $deferred->resolve($resourceStream->write($data));
            }
            $this->resourceStream = $resourceStream;
            if ($this->shouldClose) {
                $this->resourceStream->close();
            }
        });
    }
    public function write(string $data) : Promise
    {
        if ($this->resourceStream) {
            return $this->resourceStream->write($data);
        }
        if ($this->error) {
            return new Failure($this->error);
        }
        if ($this->shouldClose) {
            throw new ClosedException("Stream has already been closed.");
        }
        $deferred = new Deferred();
        $this->queuedWrites->push([$data, $deferred]);
        return $deferred->promise();
    }
    public function end(string $finalData = "") : Promise
    {
        if ($this->resourceStream) {
            return $this->resourceStream->end($finalData);
        }
        if ($this->error) {
            return new Failure($this->error);
        }
        if ($this->shouldClose) {
            throw new ClosedException("Stream has already been closed.");
        }
        $deferred = new Deferred();
        $this->queuedWrites->push([$finalData, $deferred]);
        $this->shouldClose = \true;
        return $deferred->promise();
    }
    public function close()
    {
        $this->shouldClose = \true;
        if ($this->resourceStream) {
            $this->resourceStream->close();
        } elseif (!$this->queuedWrites->isEmpty()) {
            $error = new ClosedException("Stream closed.");
            do {
                list(, $deferred) = $this->queuedWrites->shift();
                $deferred->fail($error);
            } while (!$this->queuedWrites->isEmpty());
        }
    }
}
