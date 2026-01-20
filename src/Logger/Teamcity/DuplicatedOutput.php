<?php

declare(strict_types=1);

namespace Infection\Logger\Teamcity;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class DuplicatedOutput implements OutputInterface
{
    public function __construct(
        private OutputInterface $source,
        private OutputInterface $duplicate,
    ) {
    }

    public function write(iterable|string $messages, bool $newline = false, int $options = 0): void
    {
        $this->source->write($messages, $newline, $options);
        $this->duplicate->write($messages, $newline, $options);
    }

    public function writeln(iterable|string $messages, int $options = 0): void
    {
        $this->source->writeln($messages, $options);
        $this->duplicate->writeln($messages, $options);
    }

    public function setVerbosity(int $level): void
    {
        $this->source->setVerbosity($level);
        $this->duplicate->setVerbosity($level);
    }

    public function getVerbosity(): int
    {
        return $this->source->getVerbosity();
    }

    public function isQuiet(): bool
    {
        return $this->source->isQuiet();
    }

    public function isVerbose(): bool
    {
        return $this->source->isVerbose();
    }

    public function isVeryVerbose(): bool
    {
        return $this->source->isVeryVerbose();
    }

    public function isDebug(): bool
    {
        return $this->source->isDebug();
    }

    public function setDecorated(bool $decorated): void
    {
        $this->source->setDecorated($decorated);
        $this->duplicate->setDecorated($decorated);
    }

    public function isDecorated(): bool
    {
        return $this->source->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->source->setFormatter($formatter);
        $this->duplicate->setFormatter($formatter);
    }

    public function getFormatter(): OutputFormatterInterface
    {
        return $this->source->getFormatter();
    }
}