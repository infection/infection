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

use function implode;
use Infection\Logger\ConsoleLogger;
use const PHP_EOL;
use function sprintf;

/**
 * @internal
 * @final
 */
class ConsoleOutput
{
    private const RUNNING_WITH_DEBUGGER_NOTE = 'You are running Infection with %s enabled.';
    private const MIN_MSI_CAN_GET_INCREASED_NOTICE = 'The %s is %s%% percentage points over the required %s. Consider increasing the required %s percentage the next time you run Infection.';

    public function __construct(private readonly ConsoleLogger $logger)
    {
    }

    public function logVerbosityDeprecationNotice(string $valueToUse): void
    {
        $this->logger->notice(
            'Numeric versions of log-verbosity have been deprecated, please use, ' . $valueToUse . ' to keep the same result',
            ['block' => true],
        );
    }

    public function logUnknownVerbosityOption(string $default): void
    {
        $this->logger->notice(
            'Running infection with an unknown log-verbosity option, falling back to ' . $default . ' option',
            ['block' => true],
        );
    }

    public function logMinMsiCanGetIncreasedNotice(float $minMsi, float $msi): void
    {
        $typeString = 'MSI';
        $msiDifference = $msi - $minMsi;

        $this->logger->notice(
            sprintf(
                self::MIN_MSI_CAN_GET_INCREASED_NOTICE,
                $typeString,
                $msiDifference,
                $typeString,
                $typeString,
            ),
            ['block' => true],
        );
    }

    public function logMinCoveredCodeMsiCanGetIncreasedNotice(float $minMsi, float $coveredCodeMsi): void
    {
        $typeString = 'Covered Code MSI';
        $msiDifference = $coveredCodeMsi - $minMsi;

        $this->logger->notice(
            sprintf(
                self::MIN_MSI_CAN_GET_INCREASED_NOTICE,
                $typeString,
                $msiDifference,
                $typeString,
                $typeString,
            ),
            ['block' => true],
        );
    }

    public function logRunningWithDebugger(string $debugger): void
    {
        $this->logger->notice(sprintf(self::RUNNING_WITH_DEBUGGER_NOTE, $debugger));
    }

    public function logNotInControlOfExitCodes(): void
    {
        $this->logger->warning(
            'Infection cannot control exit codes and unable to relaunch itself.' . PHP_EOL
            . 'It is your responsibility to disable xdebug/phpdbg unless needed.',
            ['block' => true],
        );
    }

    public function logSkippingInitialTests(): void
    {
        $this->logger->warning(implode(
            PHP_EOL,
            [
                'Skipping the initial test run can be very dangerous.',
                'It is your responsibility to ensure the tests are in a passing state to begin.',
                'If this is not done then mutations may report as caught when they are not.',
            ],
        ));
    }
}
