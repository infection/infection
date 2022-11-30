<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Output;

class BufferedOutput extends Output
{
    private string $buffer = '';
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
    }
}
