<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
final class ResourceOutputStream implements OutputStream
{
    const MAX_CONSECUTIVE_EMPTY_WRITES = 3;
    const LARGE_CHUNK_SIZE = 128 * 1024;
    private $resource;
    private $watcher;
    private $writes;
    private $writable = \true;
    private $chunkSize;
    public function __construct($stream, int $chunkSize = null)
    {
        if (!\is_resource($stream) || \get_resource_type($stream) !== 'stream') {
            throw new \Error("Expected a valid stream");
        }
        $meta = \stream_get_meta_data($stream);
        if (\strpos($meta["mode"], "r") !== \false && \strpos($meta["mode"], "+") === \false) {
            throw new \Error("Expected a writable stream");
        }
        \stream_set_blocking($stream, \false);
        \stream_set_write_buffer($stream, 0);
        $this->resource = $stream;
        $this->chunkSize =& $chunkSize;
        $writes = $this->writes = new \SplQueue();
        $writable =& $this->writable;
        $resource =& $this->resource;
        $this->watcher = Loop::onWritable($stream, static function ($watcher, $stream) use($writes, &$chunkSize, &$writable, &$resource) {
            static $emptyWrites = 0;
            try {
                while (!$writes->isEmpty()) {
                    list($data, $previous, $deferred) = $writes->shift();
                    $length = \strlen($data);
                    if ($length === 0) {
                        $deferred->resolve(0);
                        continue;
                    }
                    if (!\is_resource($stream) || ($metaData = @\stream_get_meta_data($stream)) && $metaData['eof']) {
                        throw new ClosedException("The stream was closed by the peer");
                    }
                    if ($chunkSize) {
                        $written = @\fwrite($stream, $data, $chunkSize);
                    } else {
                        $written = @\fwrite($stream, $data);
                    }
                    \assert($written !== \false || \PHP_VERSION_ID >= 70400, "Trying to write on a previously fclose()'d resource. Do NOT manually fclose() resources the still referenced in the loop.");
                    if ($written === \false && \PHP_VERSION_ID >= 70402) {
                        $message = "Failed to write to stream";
                        if ($error = \error_get_last()) {
                            $message .= \sprintf("; %s", $error["message"]);
                        }
                        throw new StreamException($message);
                    }
                    if ($written === 0 || $written === \false) {
                        if ($emptyWrites++ > self::MAX_CONSECUTIVE_EMPTY_WRITES) {
                            $message = "Failed to write to stream after multiple attempts";
                            if ($error = \error_get_last()) {
                                $message .= \sprintf("; %s", $error["message"]);
                            }
                            throw new StreamException($message);
                        }
                        $writes->unshift([$data, $previous, $deferred]);
                        return;
                    }
                    $emptyWrites = 0;
                    if ($length > $written) {
                        $data = \substr($data, $written);
                        $writes->unshift([$data, $written + $previous, $deferred]);
                        return;
                    }
                    $deferred->resolve($written + $previous);
                }
            } catch (\Throwable $exception) {
                $resource = null;
                $writable = \false;
                /**
                @psalm-suppress */
                $deferred->fail($exception);
                while (!$writes->isEmpty()) {
                    list(, , $deferred) = $writes->shift();
                    $deferred->fail($exception);
                }
                Loop::cancel($watcher);
            } finally {
                if ($writes->isEmpty()) {
                    Loop::disable($watcher);
                }
            }
        });
        Loop::disable($this->watcher);
    }
    public function write(string $data) : Promise
    {
        return $this->send($data, \false);
    }
    public function end(string $finalData = "") : Promise
    {
        return $this->send($finalData, \true);
    }
    private function send(string $data, bool $end = \false) : Promise
    {
        if (!$this->writable) {
            return new Failure(new ClosedException("The stream is not writable"));
        }
        $length = \strlen($data);
        $written = 0;
        if ($end) {
            $this->writable = \false;
        }
        if ($this->writes->isEmpty()) {
            if ($length === 0) {
                if ($end) {
                    $this->close();
                }
                return new Success(0);
            }
            if (!\is_resource($this->resource) || ($metaData = @\stream_get_meta_data($this->resource)) && $metaData['eof']) {
                return new Failure(new ClosedException("The stream was closed by the peer"));
            }
            if ($this->chunkSize) {
                $written = @\fwrite($this->resource, $data, $this->chunkSize);
            } else {
                $written = @\fwrite($this->resource, $data);
            }
            \assert($written !== \false || \PHP_VERSION_ID >= 70400, "Trying to write on a previously fclose()'d resource. Do NOT manually fclose() resources the still referenced in the loop.");
            if ($written === \false && \PHP_VERSION_ID >= 70402) {
                $message = "Failed to write to stream";
                if ($error = \error_get_last()) {
                    $message .= \sprintf("; %s", $error["message"]);
                }
                return new Failure(new StreamException($message));
            }
            $written = (int) $written;
            if ($length === $written) {
                if ($end) {
                    $this->close();
                }
                return new Success($written);
            }
            $data = \substr($data, $written);
        }
        $deferred = new Deferred();
        if ($length - $written > self::LARGE_CHUNK_SIZE) {
            $chunks = \str_split($data, self::LARGE_CHUNK_SIZE);
            $data = \array_pop($chunks);
            foreach ($chunks as $chunk) {
                $this->writes->push([$chunk, $written, new Deferred()]);
                $written += self::LARGE_CHUNK_SIZE;
            }
        }
        $this->writes->push([$data, $written, $deferred]);
        Loop::enable($this->watcher);
        $promise = $deferred->promise();
        if ($end) {
            $promise->onResolve([$this, "close"]);
        }
        return $promise;
    }
    public function close()
    {
        if (\is_resource($this->resource)) {
            $meta = @\stream_get_meta_data($this->resource);
            if ($meta && \strpos($meta["mode"], "+") !== \false) {
                @\stream_socket_shutdown($this->resource, \STREAM_SHUT_WR);
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
        $this->resource = null;
        $this->writable = \false;
        if (!$this->writes->isEmpty()) {
            $exception = new ClosedException("The socket was closed before writing completed");
            do {
                list(, , $deferred) = $this->writes->shift();
                $deferred->fail($exception);
            } while (!$this->writes->isEmpty());
        }
        Loop::cancel($this->watcher);
    }
    public function getResource()
    {
        return $this->resource;
    }
    public function setChunkSize(int $chunkSize)
    {
        $this->chunkSize = $chunkSize;
    }
    public function __destruct()
    {
        if ($this->resource !== null) {
            $this->free();
        }
    }
}
