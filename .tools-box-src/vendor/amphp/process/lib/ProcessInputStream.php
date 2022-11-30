<?php

namespace _HumbugBoxb47773b41c19\Amp\Process;

use _HumbugBoxb47773b41c19\Amp\ByteStream\InputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\PendingReadError;
use _HumbugBoxb47773b41c19\Amp\ByteStream\ResourceInputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\StreamException;
use _HumbugBoxb47773b41c19\Amp\Deferred;
use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
final class ProcessInputStream implements InputStream
{
    private $initialRead;
    private $shouldClose = \false;
    private $referenced = \true;
    private $resourceStream;
    private $error;
    public function __construct(Promise $resourceStreamPromise)
    {
        $resourceStreamPromise->onResolve(function ($error, $resourceStream) {
            if ($error) {
                $this->error = new StreamException("Failed to launch process", 0, $error);
                if ($this->initialRead) {
                    $initialRead = $this->initialRead;
                    $this->initialRead = null;
                    $initialRead->fail($this->error);
                }
                return;
            }
            $this->resourceStream = $resourceStream;
            if (!$this->referenced) {
                $this->resourceStream->unreference();
            }
            if ($this->shouldClose) {
                $this->resourceStream->close();
            }
            if ($this->initialRead) {
                $initialRead = $this->initialRead;
                $this->initialRead = null;
                $initialRead->resolve($this->shouldClose ? null : $this->resourceStream->read());
            }
        });
    }
    public function read() : Promise
    {
        if ($this->initialRead) {
            throw new PendingReadError();
        }
        if ($this->error) {
            return new Failure($this->error);
        }
        if ($this->resourceStream) {
            return $this->resourceStream->read();
        }
        if ($this->shouldClose) {
            return new Success();
        }
        $this->initialRead = new Deferred();
        return $this->initialRead->promise();
    }
    public function reference()
    {
        $this->referenced = \true;
        if ($this->resourceStream) {
            $this->resourceStream->reference();
        }
    }
    public function unreference()
    {
        $this->referenced = \false;
        if ($this->resourceStream) {
            $this->resourceStream->unreference();
        }
    }
    public function close()
    {
        $this->shouldClose = \true;
        if ($this->initialRead) {
            $initialRead = $this->initialRead;
            $this->initialRead = null;
            $initialRead->resolve();
        }
        if ($this->resourceStream) {
            $this->resourceStream->close();
        }
    }
}
