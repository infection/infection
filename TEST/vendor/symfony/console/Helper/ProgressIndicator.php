<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\LogicException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
class ProgressIndicator
{
    private const FORMATS = ['normal' => ' %indicator% %message%', 'normal_no_ansi' => ' %message%', 'verbose' => ' %indicator% %message% (%elapsed:6s%)', 'verbose_no_ansi' => ' %message% (%elapsed:6s%)', 'very_verbose' => ' %indicator% %message% (%elapsed:6s%, %memory:6s%)', 'very_verbose_no_ansi' => ' %message% (%elapsed:6s%, %memory:6s%)'];
    private $output;
    private $startTime;
    private $format;
    private $message;
    private $indicatorValues;
    private $indicatorCurrent;
    private $indicatorChangeInterval;
    private $indicatorUpdateTime;
    private $started = \false;
    private static $formatters;
    public function __construct(OutputInterface $output, string $format = null, int $indicatorChangeInterval = 100, array $indicatorValues = null)
    {
        $this->output = $output;
        if (null === $format) {
            $format = $this->determineBestFormat();
        }
        if (null === $indicatorValues) {
            $indicatorValues = ['-', '\\', '|', '/'];
        }
        $indicatorValues = \array_values($indicatorValues);
        if (2 > \count($indicatorValues)) {
            throw new InvalidArgumentException('Must have at least 2 indicator value characters.');
        }
        $this->format = self::getFormatDefinition($format);
        $this->indicatorChangeInterval = $indicatorChangeInterval;
        $this->indicatorValues = $indicatorValues;
        $this->startTime = \time();
    }
    public function setMessage(?string $message)
    {
        $this->message = $message;
        $this->display();
    }
    public function start(string $message)
    {
        if ($this->started) {
            throw new LogicException('Progress indicator already started.');
        }
        $this->message = $message;
        $this->started = \true;
        $this->startTime = \time();
        $this->indicatorUpdateTime = $this->getCurrentTimeInMilliseconds() + $this->indicatorChangeInterval;
        $this->indicatorCurrent = 0;
        $this->display();
    }
    public function advance()
    {
        if (!$this->started) {
            throw new LogicException('Progress indicator has not yet been started.');
        }
        if (!$this->output->isDecorated()) {
            return;
        }
        $currentTime = $this->getCurrentTimeInMilliseconds();
        if ($currentTime < $this->indicatorUpdateTime) {
            return;
        }
        $this->indicatorUpdateTime = $currentTime + $this->indicatorChangeInterval;
        ++$this->indicatorCurrent;
        $this->display();
    }
    public function finish(string $message)
    {
        if (!$this->started) {
            throw new LogicException('Progress indicator has not yet been started.');
        }
        $this->message = $message;
        $this->display();
        $this->output->writeln('');
        $this->started = \false;
    }
    public static function getFormatDefinition(string $name)
    {
        return self::FORMATS[$name] ?? null;
    }
    public static function setPlaceholderFormatterDefinition(string $name, callable $callable)
    {
        if (!self::$formatters) {
            self::$formatters = self::initPlaceholderFormatters();
        }
        self::$formatters[$name] = $callable;
    }
    public static function getPlaceholderFormatterDefinition(string $name)
    {
        if (!self::$formatters) {
            self::$formatters = self::initPlaceholderFormatters();
        }
        return self::$formatters[$name] ?? null;
    }
    private function display()
    {
        if (OutputInterface::VERBOSITY_QUIET === $this->output->getVerbosity()) {
            return;
        }
        $this->overwrite(\preg_replace_callback("{%([a-z\\-_]+)(?:\\:([^%]+))?%}i", function ($matches) {
            if ($formatter = self::getPlaceholderFormatterDefinition($matches[1])) {
                return $formatter($this);
            }
            return $matches[0];
        }, $this->format ?? ''));
    }
    private function determineBestFormat() : string
    {
        switch ($this->output->getVerbosity()) {
            case OutputInterface::VERBOSITY_VERBOSE:
                return $this->output->isDecorated() ? 'verbose' : 'verbose_no_ansi';
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
            case OutputInterface::VERBOSITY_DEBUG:
                return $this->output->isDecorated() ? 'very_verbose' : 'very_verbose_no_ansi';
            default:
                return $this->output->isDecorated() ? 'normal' : 'normal_no_ansi';
        }
    }
    private function overwrite(string $message)
    {
        if ($this->output->isDecorated()) {
            $this->output->write("\r\x1b[2K");
            $this->output->write($message);
        } else {
            $this->output->writeln($message);
        }
    }
    private function getCurrentTimeInMilliseconds() : float
    {
        return \round(\microtime(\true) * 1000);
    }
    private static function initPlaceholderFormatters() : array
    {
        return ['indicator' => function (self $indicator) {
            return $indicator->indicatorValues[$indicator->indicatorCurrent % \count($indicator->indicatorValues)];
        }, 'message' => function (self $indicator) {
            return $indicator->message;
        }, 'elapsed' => function (self $indicator) {
            return Helper::formatTime(\time() - $indicator->startTime);
        }, 'memory' => function () {
            return Helper::formatMemory(\memory_get_usage(\true));
        }];
    }
}
