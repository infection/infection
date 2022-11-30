<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Output;

class BufferedOutput extends Output
{
    private $buffer = '';
    public function fetch()
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
