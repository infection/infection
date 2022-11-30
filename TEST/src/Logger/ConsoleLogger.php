<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

use DateTime;
use DateTimeInterface;
use function gettype;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use function is_object;
use function is_scalar;
use function method_exists;
use _HumbugBox9658796bb9f0\Psr\Log\AbstractLogger;
use _HumbugBox9658796bb9f0\Psr\Log\LogLevel;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_contains;
use function strtr;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class ConsoleLogger extends AbstractLogger
{
    private const INFO = 'info';
    private const ERROR = 'error';
    private const VERBOSITY_LEVEL_MAP = [LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL, LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL, LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL, LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL, LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL, LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL, LogLevel::INFO => OutputInterface::VERBOSITY_VERBOSE, LogLevel::DEBUG => OutputInterface::VERBOSITY_DEBUG];
    private const FORMAT_LEVEL_MAP = [LogLevel::EMERGENCY => self::ERROR, LogLevel::ALERT => self::ERROR, LogLevel::CRITICAL => self::ERROR, LogLevel::ERROR => self::ERROR, LogLevel::WARNING => self::INFO, LogLevel::NOTICE => self::INFO, LogLevel::INFO => self::INFO, LogLevel::DEBUG => self::INFO];
    private const IO_MAP = [LogLevel::ERROR => 'error', LogLevel::WARNING => 'warning', LogLevel::NOTICE => 'note'];
    public function __construct(private IO $io)
    {
    }
    public function log($level, $message, array $context = []) : void
    {
        Assert::keyExists(self::VERBOSITY_LEVEL_MAP, $level, 'The log level %s does not exist');
        $output = $this->io->getOutput();
        if ($output->getVerbosity() < self::VERBOSITY_LEVEL_MAP[$level]) {
            return;
        }
        $interpolatedMessage = $this->interpolate($message, $context);
        if (!isset($context['block'])) {
            $output->writeln(sprintf('<%1$s>[%2$s] %3$s</%1$s>', self::FORMAT_LEVEL_MAP[$level], $level, $interpolatedMessage), self::VERBOSITY_LEVEL_MAP[$level]);
            return;
        }
        Assert::keyExists(self::IO_MAP, $level, 'The log level "%s" does not exist for the IO mapping');
        $this->io->{self::IO_MAP[$level]}($interpolatedMessage);
    }
    private function interpolate(string $message, array $context) : string
    {
        if (!str_contains($message, '{')) {
            return $message;
        }
        $replacements = [];
        foreach ($context as $key => $val) {
            if ($val === null || is_scalar($val) || is_object($val) && method_exists($val, '__toString')) {
                $replacements["{{$key}}"] = $val;
            } elseif ($val instanceof DateTimeInterface) {
                $replacements["{{$key}}"] = $val->format(DateTime::RFC3339);
            } elseif (is_object($val)) {
                $replacements["{{$key}}"] = '[object ' . $val::class . ']';
            } else {
                $replacements["{{$key}}"] = '[' . gettype($val) . ']';
            }
        }
        return strtr($message, $replacements);
    }
}
