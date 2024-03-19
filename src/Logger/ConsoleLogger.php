<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Logger;

use DateTime;
use DateTimeInterface;
use function gettype;
use Infection\Console\IO;
use function is_object;
use function is_scalar;
use function method_exists;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use function sprintf;
use function str_contains;
use function strtr;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ConsoleLogger extends AbstractLogger
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

    private const IO_MAP = [
        LogLevel::ERROR => 'error',
        LogLevel::WARNING => 'warning',
        LogLevel::NOTICE => 'note',
    ];

    public function __construct(private readonly IO $io)
    {
    }

    /**
     * @param string $level
     * @param string $message
     * @param mixed[] $context
     */
    public function log($level, $message, array $context = []): void
    {
        Assert::keyExists(
            self::VERBOSITY_LEVEL_MAP,
            $level,
            'The log level %s does not exist',
        );

        $output = $this->io->getOutput();

        // The if condition check isn't necessary per se – it's the same one that $output will do
        // internally anyway. We only do it for efficiency here as the message formatting is
        // relatively expensive
        if ($output->getVerbosity() < self::VERBOSITY_LEVEL_MAP[$level]) {
            return;
        }

        $interpolatedMessage = $this->interpolate($message, $context);

        if (!isset($context['block'])) {
            $output->writeln(
                sprintf(
                    '<%1$s>[%2$s] %3$s</%1$s>',
                    self::FORMAT_LEVEL_MAP[$level],
                    $level,
                    $interpolatedMessage,
                ),
                self::VERBOSITY_LEVEL_MAP[$level],
            );

            return;
        }

        Assert::keyExists(
            self::IO_MAP,
            $level,
            'The log level "%s" does not exist for the IO mapping',
        );

        $this->io->{self::IO_MAP[$level]}($interpolatedMessage);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param mixed[] $context
     *
     * @author PHP Framework Interoperability Group
     */
    private function interpolate(string $message, array $context): string
    {
        if (!str_contains($message, '{')) {
            return $message;
        }

        $replacements = [];

        foreach ($context as $key => $val) {
            if ($val === null
                || is_scalar($val)
                || (is_object($val) && method_exists($val, '__toString'))
            ) {
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
