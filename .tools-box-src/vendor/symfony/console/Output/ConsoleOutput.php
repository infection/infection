<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Output;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter\OutputFormatterInterface;
class ConsoleOutput extends StreamOutput implements ConsoleOutputInterface
{
    private OutputInterface $stderr;
    private array $consoleSectionOutputs = [];
    public function __construct(int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = null, OutputFormatterInterface $formatter = null)
    {
        parent::__construct($this->openOutputStream(), $verbosity, $decorated, $formatter);
        if (null === $formatter) {
            $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated);
            return;
        }
        $actualDecorated = $this->isDecorated();
        $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated, $this->getFormatter());
        if (null === $decorated) {
            $this->setDecorated($actualDecorated && $this->stderr->isDecorated());
        }
    }
    public function section() : ConsoleSectionOutput
    {
        return new ConsoleSectionOutput($this->getStream(), $this->consoleSectionOutputs, $this->getVerbosity(), $this->isDecorated(), $this->getFormatter());
    }
    public function setDecorated(bool $decorated)
    {
        parent::setDecorated($decorated);
        $this->stderr->setDecorated($decorated);
    }
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        parent::setFormatter($formatter);
        $this->stderr->setFormatter($formatter);
    }
    public function setVerbosity(int $level)
    {
        parent::setVerbosity($level);
        $this->stderr->setVerbosity($level);
    }
    public function getErrorOutput() : OutputInterface
    {
        return $this->stderr;
    }
    public function setErrorOutput(OutputInterface $error)
    {
        $this->stderr = $error;
    }
    protected function hasStdoutSupport() : bool
    {
        return \false === $this->isRunningOS400();
    }
    protected function hasStderrSupport() : bool
    {
        return \false === $this->isRunningOS400();
    }
    private function isRunningOS400() : bool
    {
        $checks = [\function_exists('php_uname') ? \php_uname('s') : '', \getenv('OSTYPE'), \PHP_OS];
        return \false !== \stripos(\implode(';', $checks), 'OS400');
    }
    private function openOutputStream()
    {
        if (!$this->hasStdoutSupport()) {
            return \fopen('php://output', 'w');
        }
        return \defined('STDOUT') ? \STDOUT : (@\fopen('php://stdout', 'w') ?: \fopen('php://output', 'w'));
    }
    private function openErrorStream()
    {
        if (!$this->hasStderrSupport()) {
            return \fopen('php://output', 'w');
        }
        return \defined('STDERR') ? \STDERR : (@\fopen('php://stderr', 'w') ?: \fopen('php://output', 'w'));
    }
}
