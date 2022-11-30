<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Output;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatter;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatterInterface;
abstract class Output implements OutputInterface
{
    private $verbosity;
    private $formatter;
    public function __construct(?int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = \false, OutputFormatterInterface $formatter = null)
    {
        $this->verbosity = $verbosity ?? self::VERBOSITY_NORMAL;
        $this->formatter = $formatter ?? new OutputFormatter();
        $this->formatter->setDecorated($decorated);
    }
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }
    public function getFormatter()
    {
        return $this->formatter;
    }
    public function setDecorated(bool $decorated)
    {
        $this->formatter->setDecorated($decorated);
    }
    public function isDecorated()
    {
        return $this->formatter->isDecorated();
    }
    public function setVerbosity(int $level)
    {
        $this->verbosity = $level;
    }
    public function getVerbosity()
    {
        return $this->verbosity;
    }
    public function isQuiet()
    {
        return self::VERBOSITY_QUIET === $this->verbosity;
    }
    public function isVerbose()
    {
        return self::VERBOSITY_VERBOSE <= $this->verbosity;
    }
    public function isVeryVerbose()
    {
        return self::VERBOSITY_VERY_VERBOSE <= $this->verbosity;
    }
    public function isDebug()
    {
        return self::VERBOSITY_DEBUG <= $this->verbosity;
    }
    public function writeln($messages, int $options = self::OUTPUT_NORMAL)
    {
        $this->write($messages, \true, $options);
    }
    public function write($messages, bool $newline = \false, int $options = self::OUTPUT_NORMAL)
    {
        if (!\is_iterable($messages)) {
            $messages = [$messages];
        }
        $types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
        $type = $types & $options ?: self::OUTPUT_NORMAL;
        $verbosities = self::VERBOSITY_QUIET | self::VERBOSITY_NORMAL | self::VERBOSITY_VERBOSE | self::VERBOSITY_VERY_VERBOSE | self::VERBOSITY_DEBUG;
        $verbosity = $verbosities & $options ?: self::VERBOSITY_NORMAL;
        if ($verbosity > $this->getVerbosity()) {
            return;
        }
        foreach ($messages as $message) {
            switch ($type) {
                case OutputInterface::OUTPUT_NORMAL:
                    $message = $this->formatter->format($message);
                    break;
                case OutputInterface::OUTPUT_RAW:
                    break;
                case OutputInterface::OUTPUT_PLAIN:
                    $message = \strip_tags($this->formatter->format($message));
                    break;
            }
            $this->doWrite($message ?? '', $newline);
        }
    }
    protected abstract function doWrite(string $message, bool $newline);
}
