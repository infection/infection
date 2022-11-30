<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Output;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter\OutputFormatterInterface;
class TrimmedBufferOutput extends Output
{
    private int $maxLength;
    private string $buffer = '';
    public function __construct(int $maxLength, ?int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = \false, OutputFormatterInterface $formatter = null)
    {
        if ($maxLength <= 0) {
            throw new InvalidArgumentException(\sprintf('"%s()" expects a strictly positive maxLength. Got %d.', __METHOD__, $maxLength));
        }
        parent::__construct($verbosity, $decorated, $formatter);
        $this->maxLength = $maxLength;
    }
    public function fetch() : string
    {
        $content = $this->buffer;
        $this->buffer = '';
        return $content;
    }
    protected function doWrite(string $message, bool $newline)
    {
        $this->buffer .= $message;
        if ($newline) {
            $this->buffer .= \PHP_EOL;
        }
        $this->buffer = \substr($this->buffer, 0 - $this->maxLength);
    }
}
