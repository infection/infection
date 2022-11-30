<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
use function _HumbugBoxb47773b41c19\Amp\call;
final class InputStreamChain implements InputStream
{
    private $streams;
    private $reading = \false;
    public function __construct(InputStream ...$streams)
    {
        $this->streams = $streams;
    }
    public function read() : Promise
    {
        if ($this->reading) {
            throw new PendingReadError();
        }
        if (!$this->streams) {
            return new Success(null);
        }
        return call(function () {
            $this->reading = \true;
            try {
                while ($this->streams) {
                    $chunk = (yield $this->streams[0]->read());
                    if ($chunk === null) {
                        \array_shift($this->streams);
                        continue;
                    }
                    return $chunk;
                }
                return null;
            } finally {
                $this->reading = \false;
            }
        });
    }
}
