<?php

declare(strict_types=1);

namespace Infection\Logger\Console;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
use function sprintf;

// Differs from ConsoleLogger in the fact that:
// - it gets the errorOutput earlier
// - do not change the format and discards the context
final class BasicConsoleLogger extends AbstractLogger implements LoggerInterface
{
    private const INFO = 'info';
    private const ERROR = 'error';

    private const VERBOSITY_LEVEL_MAP = [
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::DEBUG => OutputInterface::VERBOSITY_DEBUG,
    ];

    private const FORMAT_LEVEL_MAP = [
        LogLevel::EMERGENCY => self::ERROR,
        LogLevel::ALERT => self::ERROR,
        LogLevel::CRITICAL => self::ERROR,
        LogLevel::ERROR => self::ERROR,
        LogLevel::WARNING => self::INFO,
        LogLevel::NOTICE => self::INFO,
        LogLevel::INFO => self::INFO,
        LogLevel::DEBUG => self::INFO,
    ];

    private readonly OutputInterface $errorOutput;

    public function __construct(
        private readonly OutputInterface $output,
    ) {
        $this->errorOutput = $output instanceof ConsoleOutputInterface
            ? $output->getErrorOutput()
            : $output;
    }
    
    public function log($level, Stringable|string $message, array $context = []): void
    {
        Assert::keyExists(
            self::VERBOSITY_LEVEL_MAP,
            $level,
            'The log level %s does not exist',
        );

        $output = self::ERROR === self::FORMAT_LEVEL_MAP[$level]
            ? $this->errorOutput
            : $this->output;

        if ($output->getVerbosity() >= self::VERBOSITY_LEVEL_MAP[$level]) {
            $output->write(
                $message,
                options: self::VERBOSITY_LEVEL_MAP[$level],
            );
        }
    }
}