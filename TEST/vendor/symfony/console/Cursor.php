<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class Cursor
{
    private $output;
    private $input;
    public function __construct(OutputInterface $output, $input = null)
    {
        $this->output = $output;
        $this->input = $input ?? (\defined('STDIN') ? \STDIN : \fopen('php://input', 'r+'));
    }
    public function moveUp(int $lines = 1) : self
    {
        $this->output->write(\sprintf("\x1b[%dA", $lines));
        return $this;
    }
    public function moveDown(int $lines = 1) : self
    {
        $this->output->write(\sprintf("\x1b[%dB", $lines));
        return $this;
    }
    public function moveRight(int $columns = 1) : self
    {
        $this->output->write(\sprintf("\x1b[%dC", $columns));
        return $this;
    }
    public function moveLeft(int $columns = 1) : self
    {
        $this->output->write(\sprintf("\x1b[%dD", $columns));
        return $this;
    }
    public function moveToColumn(int $column) : self
    {
        $this->output->write(\sprintf("\x1b[%dG", $column));
        return $this;
    }
    public function moveToPosition(int $column, int $row) : self
    {
        $this->output->write(\sprintf("\x1b[%d;%dH", $row + 1, $column));
        return $this;
    }
    public function savePosition() : self
    {
        $this->output->write("\x1b7");
        return $this;
    }
    public function restorePosition() : self
    {
        $this->output->write("\x1b8");
        return $this;
    }
    public function hide() : self
    {
        $this->output->write("\x1b[?25l");
        return $this;
    }
    public function show() : self
    {
        $this->output->write("\x1b[?25h\x1b[?0c");
        return $this;
    }
    public function clearLine() : self
    {
        $this->output->write("\x1b[2K");
        return $this;
    }
    public function clearLineAfter() : self
    {
        $this->output->write("\x1b[K");
        return $this;
    }
    public function clearOutput() : self
    {
        $this->output->write("\x1b[0J");
        return $this;
    }
    public function clearScreen() : self
    {
        $this->output->write("\x1b[2J");
        return $this;
    }
    public function getCurrentPosition() : array
    {
        static $isTtySupported;
        if (null === $isTtySupported && \function_exists('proc_open')) {
            $isTtySupported = (bool) @\proc_open('echo 1 >/dev/null', [['file', '/dev/tty', 'r'], ['file', '/dev/tty', 'w'], ['file', '/dev/tty', 'w']], $pipes);
        }
        if (!$isTtySupported) {
            return [1, 1];
        }
        $sttyMode = \shell_exec('stty -g');
        \shell_exec('stty -icanon -echo');
        @\fwrite($this->input, "\x1b[6n");
        $code = \trim(\fread($this->input, 1024));
        \shell_exec(\sprintf('stty %s', $sttyMode));
        \sscanf($code, "\x1b[%d;%dR", $row, $col);
        return [$col, $row];
    }
}
