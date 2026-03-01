<?php

declare(strict_types=1);

namespace Infection\Container;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Webmozart\Assert\Assert;
use function file_put_contents;
use function fopen;
use function func_get_args;
use function touch;

/**
 * @method bool isSilent()
 */
final class DebugOutput implements OutputInterface, ConsoleOutputInterface
{
    private const DEBUG_FILE = __DIR__.'/../../var/teamcity/debug.log';
    private StreamOutput $debugStream;

    public function __construct(
        private OutputInterface&ConsoleOutputInterface $output,
    ) {
        $this->debugStream = new StreamOutput(
            fopen(self::DEBUG_FILE, 'a'),
        );
    }

    public static function reset(): void
    {
        file_put_contents(self::DEBUG_FILE, '');
    }

    public function getErrorOutput(): OutputInterface
    {
        return $this->output->getErrorOutput();
    }

    public function setErrorOutput(OutputInterface $error): void
    {
        $this->output->setErrorOutput($error);
    }

    public function section(): ConsoleSectionOutput
    {
        return $this->output->section();
    }

    public function write(iterable|string $messages, bool $newline = false, int $options = 0): void
    {
        $this->output->write(...func_get_args());
        $this->debugStream->write(...func_get_args());
    }

    public function writeln(iterable|string $messages, int $options = 0): void
    {
        $this->output->writeln(...func_get_args());
        $this->debugStream->writeln(...func_get_args());
    }

    public function setVerbosity(int $level): void
    {
        $this->output->setVerbosity(...func_get_args());
        $this->debugStream->setVerbosity(...func_get_args());
    }

    public function getVerbosity(): int
    {
        return $this->output->getVerbosity();
    }

    public function isQuiet(): bool
    {
        return $this->output->isQuiet();
    }

    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }

    public function setDecorated(bool $decorated): void
    {
        $this->output->setDecorated(...func_get_args());
        $this->debugStream->setDecorated(...func_get_args());
    }

    public function isDecorated(): bool
    {
        return $this->output->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->output->setFormatter(...func_get_args());
        $this->debugStream->setFormatter(...func_get_args());
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->output->getFormatter();
    }
}