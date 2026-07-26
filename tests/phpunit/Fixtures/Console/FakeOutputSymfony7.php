<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Console;

use Infection\Tests\UnsupportedMethod;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FakeOutputSymfony7 implements OutputInterface
{
    public function write(string|iterable $messages, bool $newline = false, int $options = 0): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function writeln(string|iterable $messages, int $options = 0): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function setVerbosity(int $level): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getVerbosity(): int
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function isSilent(): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function isQuiet(): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function isVerbose(): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function isVeryVerbose(): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function isDebug(): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function setDecorated(bool $decorated): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function isDecorated(): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
