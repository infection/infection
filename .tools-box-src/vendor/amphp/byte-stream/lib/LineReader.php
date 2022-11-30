<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
final class LineReader
{
    private $delimiter;
    private $lineMode;
    private $buffer = "";
    private $source;
    public function __construct(InputStream $inputStream, string $delimiter = null)
    {
        $this->source = $inputStream;
        $this->delimiter = $delimiter === null ? "\n" : $delimiter;
        $this->lineMode = $delimiter === null;
    }
    public function readLine() : Promise
    {
        return call(function () {
            if (\false !== \strpos($this->buffer, $this->delimiter)) {
                list($line, $this->buffer) = \explode($this->delimiter, $this->buffer, 2);
                return $this->lineMode ? \rtrim($line, "\r") : $line;
            }
            while (null !== ($chunk = (yield $this->source->read()))) {
                $this->buffer .= $chunk;
                if (\false !== \strpos($this->buffer, $this->delimiter)) {
                    list($line, $this->buffer) = \explode($this->delimiter, $this->buffer, 2);
                    return $this->lineMode ? \rtrim($line, "\r") : $line;
                }
            }
            if ($this->buffer === "") {
                return null;
            }
            $line = $this->buffer;
            $this->buffer = "";
            return $this->lineMode ? \rtrim($line, "\r") : $line;
        });
    }
    public function getBuffer() : string
    {
        return $this->buffer;
    }
    public function clearBuffer()
    {
        $this->buffer = "";
    }
}
