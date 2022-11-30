<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Cursor;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\LogicException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\ConsoleOutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\ConsoleSectionOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Terminal;
final class ProgressBar
{
    public const FORMAT_VERBOSE = 'verbose';
    public const FORMAT_VERY_VERBOSE = 'very_verbose';
    public const FORMAT_DEBUG = 'debug';
    public const FORMAT_NORMAL = 'normal';
    private const FORMAT_VERBOSE_NOMAX = 'verbose_nomax';
    private const FORMAT_VERY_VERBOSE_NOMAX = 'very_verbose_nomax';
    private const FORMAT_DEBUG_NOMAX = 'debug_nomax';
    private const FORMAT_NORMAL_NOMAX = 'normal_nomax';
    private int $barWidth = 28;
    private string $barChar;
    private string $emptyBarChar = '-';
    private string $progressChar = '>';
    private ?string $format = null;
    private ?string $internalFormat = null;
    private ?int $redrawFreq = 1;
    private int $writeCount = 0;
    private float $lastWriteTime = 0;
    private float $minSecondsBetweenRedraws = 0;
    private float $maxSecondsBetweenRedraws = 1;
    private OutputInterface $output;
    private int $step = 0;
    private ?int $max = null;
    private int $startTime;
    private int $stepWidth;
    private float $percent = 0.0;
    private int $formatLineCount;
    private array $messages = [];
    private bool $overwrite = \true;
    private Terminal $terminal;
    private ?string $previousMessage = null;
    private Cursor $cursor;
    private static array $formatters;
    private static array $formats;
    public function __construct(OutputInterface $output, int $max = 0, float $minSecondsBetweenRedraws = 1 / 25)
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }
        $this->output = $output;
        $this->setMaxSteps($max);
        $this->terminal = new Terminal();
        if (0 < $minSecondsBetweenRedraws) {
            $this->redrawFreq = null;
            $this->minSecondsBetweenRedraws = $minSecondsBetweenRedraws;
        }
        if (!$this->output->isDecorated()) {
            $this->overwrite = \false;
            $this->redrawFreq = null;
        }
        $this->startTime = \time();
        $this->cursor = new Cursor($output);
    }
    public static function setPlaceholderFormatterDefinition(string $name, callable $callable) : void
    {
        self::$formatters ??= self::initPlaceholderFormatters();
        self::$formatters[$name] = $callable;
    }
    public static function getPlaceholderFormatterDefinition(string $name) : ?callable
    {
        self::$formatters ??= self::initPlaceholderFormatters();
        return self::$formatters[$name] ?? null;
    }
    public static function setFormatDefinition(string $name, string $format) : void
    {
        self::$formats ??= self::initFormats();
        self::$formats[$name] = $format;
    }
    public static function getFormatDefinition(string $name) : ?string
    {
        self::$formats ??= self::initFormats();
        return self::$formats[$name] ?? null;
    }
    public function setMessage(string $message, string $name = 'message')
    {
        $this->messages[$name] = $message;
    }
    public function getMessage(string $name = 'message')
    {
        return $this->messages[$name];
    }
    public function getStartTime() : int
    {
        return $this->startTime;
    }
    public function getMaxSteps() : int
    {
        return $this->max;
    }
    public function getProgress() : int
    {
        return $this->step;
    }
    private function getStepWidth() : int
    {
        return $this->stepWidth;
    }
    public function getProgressPercent() : float
    {
        return $this->percent;
    }
    public function getBarOffset() : float
    {
        return \floor($this->max ? $this->percent * $this->barWidth : (null === $this->redrawFreq ? (int) (\min(5, $this->barWidth / 15) * $this->writeCount) : $this->step) % $this->barWidth);
    }
    public function getEstimated() : float
    {
        if (!$this->step) {
            return 0;
        }
        return \round((\time() - $this->startTime) / $this->step * $this->max);
    }
    public function getRemaining() : float
    {
        if (!$this->step) {
            return 0;
        }
        return \round((\time() - $this->startTime) / $this->step * ($this->max - $this->step));
    }
    public function setBarWidth(int $size)
    {
        $this->barWidth = \max(1, $size);
    }
    public function getBarWidth() : int
    {
        return $this->barWidth;
    }
    public function setBarCharacter(string $char)
    {
        $this->barChar = $char;
    }
    public function getBarCharacter() : string
    {
        return $this->barChar ?? ($this->max ? '=' : $this->emptyBarChar);
    }
    public function setEmptyBarCharacter(string $char)
    {
        $this->emptyBarChar = $char;
    }
    public function getEmptyBarCharacter() : string
    {
        return $this->emptyBarChar;
    }
    public function setProgressCharacter(string $char)
    {
        $this->progressChar = $char;
    }
    public function getProgressCharacter() : string
    {
        return $this->progressChar;
    }
    public function setFormat(string $format)
    {
        $this->format = null;
        $this->internalFormat = $format;
    }
    public function setRedrawFrequency(?int $freq)
    {
        $this->redrawFreq = null !== $freq ? \max(1, $freq) : null;
    }
    public function minSecondsBetweenRedraws(float $seconds) : void
    {
        $this->minSecondsBetweenRedraws = $seconds;
    }
    public function maxSecondsBetweenRedraws(float $seconds) : void
    {
        $this->maxSecondsBetweenRedraws = $seconds;
    }
    public function iterate(iterable $iterable, int $max = null) : iterable
    {
        $this->start($max ?? (\is_countable($iterable) ? \count($iterable) : 0));
        foreach ($iterable as $key => $value) {
            (yield $key => $value);
            $this->advance();
        }
        $this->finish();
    }
    public function start(int $max = null)
    {
        $this->startTime = \time();
        $this->step = 0;
        $this->percent = 0.0;
        if (null !== $max) {
            $this->setMaxSteps($max);
        }
        $this->display();
    }
    public function advance(int $step = 1)
    {
        $this->setProgress($this->step + $step);
    }
    public function setOverwrite(bool $overwrite)
    {
        $this->overwrite = $overwrite;
    }
    public function setProgress(int $step)
    {
        if ($this->max && $step > $this->max) {
            $this->max = $step;
        } elseif ($step < 0) {
            $step = 0;
        }
        $redrawFreq = $this->redrawFreq ?? ($this->max ?: 10) / 10;
        $prevPeriod = (int) ($this->step / $redrawFreq);
        $currPeriod = (int) ($step / $redrawFreq);
        $this->step = $step;
        $this->percent = $this->max ? (float) $this->step / $this->max : 0;
        $timeInterval = \microtime(\true) - $this->lastWriteTime;
        if ($this->max === $step) {
            $this->display();
            return;
        }
        if ($timeInterval < $this->minSecondsBetweenRedraws) {
            return;
        }
        if ($prevPeriod !== $currPeriod || $timeInterval >= $this->maxSecondsBetweenRedraws) {
            $this->display();
        }
    }
    public function setMaxSteps(int $max)
    {
        $this->format = null;
        $this->max = \max(0, $max);
        $this->stepWidth = $this->max ? Helper::width((string) $this->max) : 4;
    }
    public function finish() : void
    {
        if (!$this->max) {
            $this->max = $this->step;
        }
        if ($this->step === $this->max && !$this->overwrite) {
            return;
        }
        $this->setProgress($this->max);
    }
    public function display() : void
    {
        if (OutputInterface::VERBOSITY_QUIET === $this->output->getVerbosity()) {
            return;
        }
        if (null === $this->format) {
            $this->setRealFormat($this->internalFormat ?: $this->determineBestFormat());
        }
        $this->overwrite($this->buildLine());
    }
    public function clear() : void
    {
        if (!$this->overwrite) {
            return;
        }
        if (null === $this->format) {
            $this->setRealFormat($this->internalFormat ?: $this->determineBestFormat());
        }
        $this->overwrite('');
    }
    private function setRealFormat(string $format)
    {
        if (!$this->max && null !== self::getFormatDefinition($format . '_nomax')) {
            $this->format = self::getFormatDefinition($format . '_nomax');
        } elseif (null !== self::getFormatDefinition($format)) {
            $this->format = self::getFormatDefinition($format);
        } else {
            $this->format = $format;
        }
        $this->formatLineCount = \substr_count($this->format, "\n");
    }
    private function overwrite(string $message) : void
    {
        if ($this->previousMessage === $message) {
            return;
        }
        $originalMessage = $message;
        if ($this->overwrite) {
            if (null !== $this->previousMessage) {
                if ($this->output instanceof ConsoleSectionOutput) {
                    $messageLines = \explode("\n", $message);
                    $lineCount = \count($messageLines);
                    foreach ($messageLines as $messageLine) {
                        $messageLineLength = Helper::width(Helper::removeDecoration($this->output->getFormatter(), $messageLine));
                        if ($messageLineLength > $this->terminal->getWidth()) {
                            $lineCount += \floor($messageLineLength / $this->terminal->getWidth());
                        }
                    }
                    $this->output->clear($lineCount);
                } else {
                    for ($i = 0; $i < $this->formatLineCount; ++$i) {
                        $this->cursor->moveToColumn(1);
                        $this->cursor->clearLine();
                        $this->cursor->moveUp();
                    }
                    $this->cursor->moveToColumn(1);
                    $this->cursor->clearLine();
                }
            }
        } elseif ($this->step > 0) {
            $message = \PHP_EOL . $message;
        }
        $this->previousMessage = $originalMessage;
        $this->lastWriteTime = \microtime(\true);
        $this->output->write($message);
        ++$this->writeCount;
    }
    private function determineBestFormat() : string
    {
        return match ($this->output->getVerbosity()) {
            OutputInterface::VERBOSITY_VERBOSE => $this->max ? self::FORMAT_VERBOSE : self::FORMAT_VERBOSE_NOMAX,
            OutputInterface::VERBOSITY_VERY_VERBOSE => $this->max ? self::FORMAT_VERY_VERBOSE : self::FORMAT_VERY_VERBOSE_NOMAX,
            OutputInterface::VERBOSITY_DEBUG => $this->max ? self::FORMAT_DEBUG : self::FORMAT_DEBUG_NOMAX,
            default => $this->max ? self::FORMAT_NORMAL : self::FORMAT_NORMAL_NOMAX,
        };
    }
    private static function initPlaceholderFormatters() : array
    {
        return ['bar' => function (self $bar, OutputInterface $output) {
            $completeBars = $bar->getBarOffset();
            $display = \str_repeat($bar->getBarCharacter(), $completeBars);
            if ($completeBars < $bar->getBarWidth()) {
                $emptyBars = $bar->getBarWidth() - $completeBars - Helper::length(Helper::removeDecoration($output->getFormatter(), $bar->getProgressCharacter()));
                $display .= $bar->getProgressCharacter() . \str_repeat($bar->getEmptyBarCharacter(), $emptyBars);
            }
            return $display;
        }, 'elapsed' => function (self $bar) {
            return Helper::formatTime(\time() - $bar->getStartTime());
        }, 'remaining' => function (self $bar) {
            if (!$bar->getMaxSteps()) {
                throw new LogicException('Unable to display the remaining time if the maximum number of steps is not set.');
            }
            return Helper::formatTime($bar->getRemaining());
        }, 'estimated' => function (self $bar) {
            if (!$bar->getMaxSteps()) {
                throw new LogicException('Unable to display the estimated time if the maximum number of steps is not set.');
            }
            return Helper::formatTime($bar->getEstimated());
        }, 'memory' => function (self $bar) {
            return Helper::formatMemory(\memory_get_usage(\true));
        }, 'current' => function (self $bar) {
            return \str_pad($bar->getProgress(), $bar->getStepWidth(), ' ', \STR_PAD_LEFT);
        }, 'max' => function (self $bar) {
            return $bar->getMaxSteps();
        }, 'percent' => function (self $bar) {
            return \floor($bar->getProgressPercent() * 100);
        }];
    }
    private static function initFormats() : array
    {
        return [self::FORMAT_NORMAL => ' %current%/%max% [%bar%] %percent:3s%%', self::FORMAT_NORMAL_NOMAX => ' %current% [%bar%]', self::FORMAT_VERBOSE => ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%', self::FORMAT_VERBOSE_NOMAX => ' %current% [%bar%] %elapsed:6s%', self::FORMAT_VERY_VERBOSE => ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%', self::FORMAT_VERY_VERBOSE_NOMAX => ' %current% [%bar%] %elapsed:6s%', self::FORMAT_DEBUG => ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%', self::FORMAT_DEBUG_NOMAX => ' %current% [%bar%] %elapsed:6s% %memory:6s%'];
    }
    private function buildLine() : string
    {
        \assert(null !== $this->format);
        $regex = "{%([a-z\\-_]+)(?:\\:([^%]+))?%}i";
        $callback = function ($matches) {
            if ($formatter = $this::getPlaceholderFormatterDefinition($matches[1])) {
                $text = $formatter($this, $this->output);
            } elseif (isset($this->messages[$matches[1]])) {
                $text = $this->messages[$matches[1]];
            } else {
                return $matches[0];
            }
            if (isset($matches[2])) {
                $text = \sprintf('%' . $matches[2], $text);
            }
            return $text;
        };
        $line = \preg_replace_callback($regex, $callback, $this->format);
        $linesLength = \array_map(function ($subLine) {
            return Helper::width(Helper::removeDecoration($this->output->getFormatter(), \rtrim($subLine, "\r")));
        }, \explode("\n", $line));
        $linesWidth = \max($linesLength);
        $terminalWidth = $this->terminal->getWidth();
        if ($linesWidth <= $terminalWidth) {
            return $line;
        }
        $this->setBarWidth($this->barWidth - $linesWidth + $terminalWidth);
        return \preg_replace_callback($regex, $callback, $this->format);
    }
}
