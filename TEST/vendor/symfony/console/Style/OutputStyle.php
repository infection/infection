<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Style;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatterInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\ProgressBar;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\ConsoleOutputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
abstract class OutputStyle implements OutputInterface, StyleInterface
{
    private $output;
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }
    public function newLine(int $count = 1)
    {
        $this->output->write(\str_repeat(\PHP_EOL, $count));
    }
    public function createProgressBar(int $max = 0)
    {
        return new ProgressBar($this->output, $max);
    }
    public function write($messages, bool $newline = \false, int $type = self::OUTPUT_NORMAL)
    {
        $this->output->write($messages, $newline, $type);
    }
    public function writeln($messages, int $type = self::OUTPUT_NORMAL)
    {
        $this->output->writeln($messages, $type);
    }
    public function setVerbosity(int $level)
    {
        $this->output->setVerbosity($level);
    }
    public function getVerbosity()
    {
        return $this->output->getVerbosity();
    }
    public function setDecorated(bool $decorated)
    {
        $this->output->setDecorated($decorated);
    }
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->output->setFormatter($formatter);
    }
    public function getFormatter()
    {
        return $this->output->getFormatter();
    }
    public function isQuiet()
    {
        return $this->output->isQuiet();
    }
    public function isVerbose()
    {
        return $this->output->isVerbose();
    }
    public function isVeryVerbose()
    {
        return $this->output->isVeryVerbose();
    }
    public function isDebug()
    {
        return $this->output->isDebug();
    }
    protected function getErrorOutput()
    {
        if (!$this->output instanceof ConsoleOutputInterface) {
            return $this->output;
        }
        return $this->output->getErrorOutput();
    }
}
