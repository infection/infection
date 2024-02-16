<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Console;

use Infection\Tests\UnsupportedMethod;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FakeOutputSymfony5 implements OutputInterface
{
    public function write($messages, $newline = false, $options = 0)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function writeln($messages, $options = 0)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function setVerbosity($level)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getVerbosity(): int
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function isQuiet(): bool
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function isVerbose(): bool
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function isVeryVerbose(): bool
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function isDebug(): bool
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function setDecorated($decorated)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function isDecorated(): bool
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function setFormatter(OutputFormatterInterface $formatter)
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }
}
