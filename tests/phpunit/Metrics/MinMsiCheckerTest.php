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

namespace Infection\Tests\Metrics;

use Infection\Console\ConsoleOutput;
use Infection\Console\IO;
use Infection\Logger\ConsoleLogger;
use Infection\Metrics\MinMsiChecker;
use Infection\Metrics\MinMsiCheckFailed;
use function Infection\Tests\normalize_trailing_spaces;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Terminal;

final class MinMsiCheckerTest extends TestCase
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
        if ((new Terminal())->getWidth() !== 100 || PHP_EOL === "\r\n") {
            $this->markTestSkipped('This test assumes 100 columns wide display and Unix line endings');
        }

        $this->output = new BufferedOutput();

        $this->consoleOutput = new ConsoleOutput(
            new ConsoleLogger(
                new IO(new StringInput(''), $this->output)
            )
        );
    }

    public function test_it_fails_the_check_if_the_msi_is_lower_than_the_min_msi(): void
    {
        $msiChecker = new MinMsiChecker(false, 10., 5.);

        try {
            $msiChecker->checkMetrics(2, 8., 10., $this->consoleOutput);

            $this->fail();
        } catch (MinMsiCheckFailed $exception) {
            $this->assertSame(
                'The minimum required MSI percentage should be 10%, but actual is 8%. Improve your tests!',
                $exception->getMessage()
            );

            $this->assertSame('', $this->output->fetch());
        }
    }

    public function test_it_fails_the_check_if_the_covered_code_msi_is_lower_than_the_min_covered_code_msi(): void
    {
        $msiChecker = new MinMsiChecker(false, 5., 10.);

        try {
            $msiChecker->checkMetrics(2, 12., 8., $this->consoleOutput);

            $this->fail();
        } catch (MinMsiCheckFailed $exception) {
            $this->assertSame(
                'The minimum required Covered Code MSI percentage should be 10%, but actual is 8%. Improve your tests!',
                $exception->getMessage()
            );

            $this->assertSame('', $this->output->fetch());
        }
    }

    public function test_it_suggests_to_increase_the_min_msi_if_above_the_limit(): void
    {
        $msiChecker = new MinMsiChecker(false, 10., 10.);

        $msiChecker->checkMetrics(2, 80., 10., $this->consoleOutput);

        $this->assertSame(
            <<<'TXT'

 ! [NOTE] The MSI is 70% percentage points over the required MSI. Consider increasing the required
 !        MSI percentage the next time you run Infection.


TXT
            ,
            normalize_trailing_spaces($this->output->fetch())
        );
    }

    public function test_it_suggests_to_increase_the_min_covered_code_msi_if_above_the_limit(): void
    {
        $msiChecker = new MinMsiChecker(false, 10., 10.);

        $msiChecker->checkMetrics(2, 10., 80., $this->consoleOutput);

        $this->assertSame(
            <<<'TXT'

 ! [NOTE] The Covered Code MSI is 70% percentage points over the required Covered Code MSI. Consider
 !        increasing the required Covered Code MSI percentage the next time you run Infection.


TXT
            ,
            normalize_trailing_spaces($this->output->fetch())
        );
    }

    public function test_it_suggests_to_increase_the_min_msi_and_min_covered_code_msi_if_above_the_limit(): void
    {
        $msiChecker = new MinMsiChecker(false, 10., 10.);

        $msiChecker->checkMetrics(2, 80., 80., $this->consoleOutput);

        $this->assertSame(
            <<<'TXT'

 ! [NOTE] The MSI is 70% percentage points over the required MSI. Consider increasing the required
 !        MSI percentage the next time you run Infection.

 ! [NOTE] The Covered Code MSI is 70% percentage points over the required Covered Code MSI. Consider
 !        increasing the required Covered Code MSI percentage the next time you run Infection.


TXT
            ,
            normalize_trailing_spaces($this->output->fetch())
        );
    }

    public function test_it_does_nothing_if_the_scores_barely_passes(): void
    {
        $msiChecker = new MinMsiChecker(false, 10., 10.);

        $msiChecker->checkMetrics(2, 10.2, 10.2, $this->consoleOutput);

        $this->assertSame('', $this->output->fetch());
    }

    public function test_it_does_nothing_if_the_scores_barely_passes_with_no_mutation(): void
    {
        $msiChecker = new MinMsiChecker(false, 10., 10.);

        $msiChecker->checkMetrics(0, 10.2, 10.2, $this->consoleOutput);

        $this->assertSame('', $this->output->fetch());
    }

    public function test_it_does_nothing_if_the_msis_are_too_low_but_we_ignore_it_with_no_mutations_and_there_is_no_mutations(): void
    {
        $msiChecker = new MinMsiChecker(true, 10., 10.);

        $msiChecker->checkMetrics(0, 2, 2, $this->consoleOutput);

        $this->assertSame('', $this->output->fetch());
    }
}
