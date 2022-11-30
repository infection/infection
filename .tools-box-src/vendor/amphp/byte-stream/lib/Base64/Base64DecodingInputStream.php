<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream\Base64;

use _HumbugBoxb47773b41c19\Amp\ByteStream\InputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\StreamException;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
final class Base64DecodingInputStream implements InputStream
{
    private $source;
    private $buffer = '';
    public function __construct(InputStream $source)
    {
        $this->source = $source;
    }
    public function read() : Promise
    {
        return call(function () {
            if ($this->source === null) {
                throw new StreamException('Failed to read stream chunk due to invalid base64 data');
            }
            $chunk = (yield $this->source->read());
            if ($chunk === null) {
                if ($this->buffer === null) {
                    return null;
                }
                $chunk = \base64_decode($this->buffer, \true);
                if ($chunk === \false) {
                    $this->source = null;
                    $this->buffer = null;
                    throw new StreamException('Failed to read stream chunk due to invalid base64 data');
                }
                $this->buffer = null;
                return $chunk;
            }
            $this->buffer .= $chunk;
            $length = \strlen($this->buffer);
            $chunk = \base64_decode(\substr($this->buffer, 0, $length - $length % 4), \true);
            if ($chunk === \false) {
                $this->source = null;
                $this->buffer = null;
                throw new StreamException('Failed to read stream chunk due to invalid base64 data');
            }
            $this->buffer = \substr($this->buffer, $length - $length % 4);
            return $chunk;
        });
    }
}
