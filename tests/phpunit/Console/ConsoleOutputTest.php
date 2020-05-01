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

namespace Infection\Tests\Console;

use Infection\Console\ConsoleOutput;
use Infection\Console\IO;
use Infection\Logger\ConsoleLogger;
use function Infection\Tests\normalize_trailing_spaces;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class ConsoleOutputTest extends TestCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var ConsoleOutput
     */
    private $consoleOutput;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();

        $this->consoleOutput = new ConsoleOutput(
            new ConsoleLogger(
                new IO(new StringInput(''), $this->output)
            )
        );
    }

    public function test_log_verbosity_deprecation_notice(): void
    {
        $this->consoleOutput->logVerbosityDeprecationNotice('all');

        $this->assertSame(
            <<<'TXT'

 ! [NOTE] Numeric versions of log-verbosity have been deprecated, please use, all to keep the same
 !        result


TXT
            ,
            normalize_trailing_spaces($this->output->fetch())
        );
    }

    public function test_log_unknown_verbosity_option(): void
    {
        $this->consoleOutput->logUnknownVerbosityOption('default');

        $this->assertSame(
            <<<'TXT'

 ! [NOTE] Running infection with an unknown log-verbosity option, falling back to default option


TXT
            ,
            normalize_trailing_spaces($this->output->fetch())
        );
    }

    public function test_log_not_in_control_of_exit_codes(): void
    {
        $this->consoleOutput->logNotInControlOfExitCodes();

        $this->assertSame(
            <<<'TXT'

 [WARNING] Infection cannot control exit codes and unable to relaunch itself.
           It is your responsibility to disable xdebug/phpdbg unless needed.


TXT
            ,
            normalize_trailing_spaces($this->output->fetch())
        );
    }

    public function test_log_min_msi_can_get_increased_notice_for_msi(): void
    {
        $this->consoleOutput->logMinMsiCanGetIncreasedNotice(
            5.0,
            10.0
        );

        $this->assertSame(
            <<<'TXT'

 ! [NOTE] The MSI is 5% percent points over the required MSI. Consider increasing the required MSI
 !        percentage the next time you run infection.


TXT
            ,
            normalize_trailing_spaces($this->output->fetch())
        );
    }

    public function test_log_min_msi_can_get_increased_notice_for_covered_msi(): void
    {
        $this->consoleOutput->logMinCoveredCodeMsiCanGetIncreasedNotice(
            5.0,
            10.0
        );

        $this->assertSame(
            <<<'TXT'

 ! [NOTE] The Covered Code MSI is 5% percent points over the required Covered Code MSI. Consider
 !        increasing the required Covered Code MSI percentage the next time you run infection.


TXT
            ,
            normalize_trailing_spaces($this->output->fetch())
        );
    }
}
