<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
final class ResourceInputStream implements InputStream
{
    const DEFAULT_CHUNK_SIZE = 8192;
    private $resource;
    private $watcher;
    private $deferred;
    private $readable = \true;
    private $chunkSize;
    private $useSingleRead;
    private $immediateCallable;
    private $immediateWatcher;
    public function __construct($stream, int $chunkSize = self::DEFAULT_CHUNK_SIZE)
    {
        if (!\is_resource($stream) || \get_resource_type($stream) !== 'stream') {
            throw new \Error("Expected a valid stream");
        }
        $meta = \stream_get_meta_data($stream);
        $useSingleRead = $meta["stream_type"] === "udp_socket" || $meta["stream_type"] === "STDIO";
        $this->useSingleRead = $useSingleRead;
        if (\strpos($meta["mode"], "r") === \false && \strpos($meta["mode"], "+") === \false) {
            throw new \Error("Expected a readable stream");
        }
        \stream_set_blocking($stream, \false);
        \stream_set_read_buffer($stream, 0);
        $this->resource =& $stream;
        $this->chunkSize =& $chunkSize;
        $deferred =& $this->deferred;
        $readable =& $this->readable;
        $this->watcher = Loop::onReadable($this->resource, static function ($watcher) use(&$deferred, &$readable, &$stream, &$chunkSize, $useSingleRead) {
            if ($useSingleRead) {
                $data = @\fread($stream, $chunkSize);
            } else {
                $data = @\stream_get_contents($stream, $chunkSize);
            }
            \assert($data !== \false, "Trying to read from a previously fclose()'d resource. Do NOT manually fclose() resources the loop still has a reference to.");
            if ($data === '' && @\feof($stream)) {
                $readable = \false;
                $stream = null;
                $data = null;
                Loop::cancel($watcher);
            } else {
                Loop::disable($watcher);
            }
            $temp = $deferred;
            $deferred = null;
            \assert($temp instanceof Deferred);
            $temp->resolve($data);
        });
        $this->immediateCallable = static function ($watcherId, $data) use(&$deferred) {
            $temp = $deferred;
            $deferred = null;
            \assert($temp instanceof Deferred);
            $temp->resolve($data);
        };
        Loop::disable($this->watcher);
    }
    public function read() : Promise
    {
        if ($this->deferred !== null) {
            throw new PendingReadError();
        }
        if (!$this->readable) {
            return new Success();
        }
        \assert($this->resource !== null);
        if ($this->useSingleRead) {
            $data = @\fread($this->resource, $this->chunkSize);
        } else {
            $data = @\stream_get_contents($this->resource, $this->chunkSize);
        }
        \assert($data !== \false, "Trying to read from a previously fclose()'d resource. Do NOT manually fclose() resources the loop still has a reference to.");
        if ($data === '') {
            if (@\feof($this->resource)) {
                $this->readable = \false;
                $this->resource = null;
                Loop::cancel($this->watcher);
                return new Success();
            }
            $this->deferred = new Deferred();
            Loop::enable($this->watcher);
            return $this->deferred->promise();
        }
        $this->deferred = new Deferred();
        $this->immediateWatcher = Loop::defer($this->immediateCallable, $data);
        return $this->deferred->promise();
    }
    public function close()
    {
        if (\is_resource($this->resource)) {
            $meta = @\stream_get_meta_data($this->resource);
            if ($meta && \strpos($meta["mode"], "+") !== \false) {
                @\stream_socket_shutdown($this->resource, \STREAM_SHUT_RD);
            } else {
                /**
                @psalm-suppress */
                @\fclose($this->resource);
            }
        }
        $this->free();
    }
    private function free()
    {
        $this->readable = \false;
        $this->resource = null;
        if ($this->deferred !== null) {
            $deferred = $this->deferred;
            $this->deferred = null;
            $deferred->resolve();
        }
        Loop::cancel($this->watcher);
        if ($this->immediateWatcher !== null) {
            Loop::cancel($this->immediateWatcher);
        }
    }
    public function getResource()
    {
        return $this->resource;
    }
    public function setChunkSize(int $chunkSize)
    {
        $this->chunkSize = $chunkSize;
    }
    public function reference()
    {
        if (!$this->resource) {
            throw new \Error("Resource has already been freed");
        }
        Loop::reference($this->watcher);
    }
    public function unreference()
    {
        if (!$this->resource) {
            throw new \Error("Resource has already been freed");
        }
        Loop::unreference($this->watcher);
    }
    public function __destruct()
    {
        if ($this->resource !== null) {
            $this->free();
        }
    }
}
