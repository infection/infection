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

namespace Infection\Console;

use function array_filter;
use function array_key_exists;
use function array_values;
use function explode;
use function in_array;
use Infection\CannotBeInstantiated;
use Infection\Mutant\DetectionStatus;
use InvalidArgumentException;
use function sprintf;
use function substr;
use Symfony\Component\Console\Input\InputInterface;
use function trim;

/**
 * @internal
 */
final class LogVerbosity
{
    use CannotBeInstantiated;

    public const string DEBUG = 'all';

    public const string NORMAL = 'default';

    public const string NONE = 'none';

    /**
     * @deprecated
     */
    public const int DEBUG_INTEGER = 1;

    /**
     * @deprecated
     */
    public const int NORMAL_INTEGER = 2;

    /**
     * @deprecated
     */
    public const int NONE_INTEGER = 3;

    /**
     * @var array<int, string>
     */
    public const array ALLOWED_OPTIONS = [
        self::DEBUG_INTEGER => self::DEBUG,
        self::NORMAL_INTEGER => self::NORMAL,
        self::NONE_INTEGER => self::NONE,
    ];

    /**
     * Map of a slug (as accepted by `--log-verbosity`) to the corresponding {@see DetectionStatus}.
     *
     * @var array<string, DetectionStatus>
     */
    public const array STATUS_SLUGS = [
        'escaped' => DetectionStatus::ESCAPED,
        'timed-out' => DetectionStatus::TIMED_OUT,
        'timeout' => DetectionStatus::TIMED_OUT,
        'skipped' => DetectionStatus::SKIPPED,
        'killed' => DetectionStatus::KILLED_BY_TESTS,
        'killed-sa' => DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
        'error' => DetectionStatus::ERROR,
        'errors' => DetectionStatus::ERROR,
        'syntax-error' => DetectionStatus::SYNTAX_ERROR,
        'not-covered' => DetectionStatus::NOT_COVERED,
        'ignored' => DetectionStatus::IGNORED,
    ];

    /**
     * Statuses displayed by default (i.e. when `--log-verbosity=default`).
     *
     * @var array<int, DetectionStatus>
     */
    public const array DEFAULT_STATUSES = [
        DetectionStatus::ESCAPED,
        DetectionStatus::TIMED_OUT,
        DetectionStatus::SKIPPED,
        DetectionStatus::NOT_COVERED,
    ];

    /**
     * Statuses displayed when `--log-verbosity=all`.
     *
     * @var array<int, DetectionStatus>
     */
    public const array ALL_STATUSES = [
        DetectionStatus::ESCAPED,
        DetectionStatus::TIMED_OUT,
        DetectionStatus::SKIPPED,
        DetectionStatus::KILLED_BY_TESTS,
        DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
        DetectionStatus::ERROR,
        DetectionStatus::SYNTAX_ERROR,
        DetectionStatus::NOT_COVERED,
        DetectionStatus::IGNORED,
    ];

    public static function convertVerbosityLevel(InputInterface $input, ConsoleOutput $io): void
    {
        $verbosityLevel = $input->getOption('log-verbosity');

        if (in_array($verbosityLevel, self::ALLOWED_OPTIONS, true) || self::isValidOption((string) $verbosityLevel)) {
            return;
        }

        // If that's non-standard, think it's a legacy numeric option.
        $verbosityLevel = (int) $verbosityLevel;

        if (array_key_exists($verbosityLevel, self::ALLOWED_OPTIONS)) {
            $input->setOption('log-verbosity', self::ALLOWED_OPTIONS[$verbosityLevel]);
            $io->logVerbosityDeprecationNotice(self::ALLOWED_OPTIONS[$verbosityLevel]);

            return;
        }

        $io->logUnknownVerbosityOption(self::NORMAL);
        $input->setOption('log-verbosity', self::NORMAL);
    }

    public static function isValidOption(string $option): bool
    {
        if (in_array($option, [self::DEBUG, self::NONE, self::NORMAL], true)) {
            return true;
        }

        foreach (explode(',', $option) as $token) {
            $token = trim($token);

            if ($token === '') {
                continue;
            }

            $sign = $token[0];
            $core = $sign === '+' || $sign === '-' ? substr($token, 1) : $token;

            if (in_array($core, [self::DEBUG, self::NONE, self::NORMAL], true)) {
                continue;
            }

            if (!array_key_exists($core, self::STATUS_SLUGS)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolves the `--log-verbosity` option into the list of {@see DetectionStatus} values to display.
     *
     * The `all`, `default` and `none` shorthands are kept for backward compatibility. Any other value is
     * interpreted as a comma-separated list of status slugs (see {@see self::STATUS_SLUGS}) where each
     * slug can be prefixed with `+` (include, the default) or `-` (exclude). The list is applied on top of
     * the `default` set, e.g. `default,-timeout` or `none,+escaped,+timeout`.
     *
     * @return array<int, DetectionStatus>
     */
    public static function resolveDisplayedStatuses(string $option): array
    {
        if ($option === self::DEBUG) {
            return self::ALL_STATUSES;
        }

        if ($option === self::NONE) {
            return [];
        }

        if ($option === self::NORMAL) {
            return self::DEFAULT_STATUSES;
        }

        $statuses = self::DEFAULT_STATUSES;

        foreach (explode(',', $option) as $token) {
            $token = trim($token);

            if ($token === '') {
                continue;
            }

            $sign = $token[0];
            $core = $sign === '+' || $sign === '-' ? substr($token, 1) : $token;

            if ($core === self::DEBUG) {
                $statuses = self::ALL_STATUSES;

                continue;
            }

            if ($core === self::NONE) {
                $statuses = [];

                continue;
            }

            if ($core === self::NORMAL) {
                $statuses = self::DEFAULT_STATUSES;

                continue;
            }

            if (!array_key_exists($core, self::STATUS_SLUGS)) {
                throw new InvalidArgumentException(sprintf('Unknown "--log-verbosity" status "%s".', $core));
            }

            $status = self::STATUS_SLUGS[$core];

            if ($sign === '-') {
                $statuses = array_values(array_filter(
                    $statuses,
                    static fn (DetectionStatus $current): bool => $current !== $status,
                ));

                continue;
            }

            if (!in_array($status, $statuses, true)) {
                $statuses[] = $status;
            }
        }

        return $statuses;
    }
}
