<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Coroutine;
use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
class Message implements InputStream, Promise
{
    private $source;
    private $buffer = "";
    private $pendingRead;
    private $coroutine;
    private $buffering = \false;
    private $backpressure;
    private $complete = \false;
    private $error;
    public function __construct(InputStream $source)
    {
        $this->source = $source;
    }
    private function consume() : \Generator
    {
        while (($chunk = (yield $this->source->read())) !== null) {
            $buffer = $this->buffer .= $chunk;
            if ($buffer === "") {
                continue;
            } elseif ($this->pendingRead) {
                $deferred = $this->pendingRead;
                $this->pendingRead = null;
                $this->buffer = "";
                $deferred->resolve($buffer);
                $buffer = "";
            } elseif (!$this->buffering) {
                $buffer = "";
                $this->backpressure = new Deferred();
                (yield $this->backpressure->promise());
            }
        }
        $this->complete = \true;
        if ($this->pendingRead) {
            $deferred = $this->pendingRead;
            $this->pendingRead = null;
            $deferred->resolve($this->buffer !== "" ? $this->buffer : null);
            $this->buffer = "";
        }
        return $this->buffer;
    }
    public final function read() : Promise
    {
        if ($this->pendingRead) {
            throw new PendingReadError();
        }
        if ($this->coroutine === null) {
            $this->coroutine = new Coroutine($this->consume());
            $this->coroutine->onResolve(function ($error) {
                if ($error) {
                    $this->error = $error;
                }
                if ($this->pendingRead) {
                    $deferred = $this->pendingRead;
                    $this->pendingRead = null;
                    $deferred->fail($error);
                }
            });
        }
        if ($this->error) {
            return new Failure($this->error);
        }
        if ($this->buffer !== "") {
            $buffer = $this->buffer;
            $this->buffer = "";
            if ($this->backpressure) {
                $backpressure = $this->backpressure;
                $this->backpressure = null;
                $backpressure->resolve();
            }
            return new Success($buffer);
        }
        if ($this->complete) {
            return new Success();
        }
        $this->pendingRead = new Deferred();
        return $this->pendingRead->promise();
    }
    public final function onResolve(callable $onResolved)
    {
        $this->buffering = \true;
        if ($this->coroutine === null) {
            $this->coroutine = new Coroutine($this->consume());
        }
        if ($this->backpressure) {
            $backpressure = $this->backpressure;
            $this->backpressure = null;
            $backpressure->resolve();
        }
        $this->coroutine->onResolve($onResolved);
    }
    public final function getInputStream() : InputStream
    {
        return $this->source;
    }
}
