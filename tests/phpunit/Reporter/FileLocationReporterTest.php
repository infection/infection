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

namespace Infection\Tests\Reporter;

use Infection\FileSystem\FakeFileSystem;
use Infection\Framework\Str;
use Infection\Reporter\FederatedReporter;
use Infection\Reporter\FileLocationReporter;
use Infection\Reporter\FileReporter;
use Infection\Reporter\Reporter;
use Infection\Tests\Fixtures\Logger\FakeLogger;
use Infection\Tests\Fixtures\Reporter\DummyLineMutationTestingResultsReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(FileLocationReporter::class)]
final class FileLocationReporterTest extends TestCase
{
    #[DataProvider('reporterProvider')]
    public function test_it_reports_the_generated_file_report_paths(
        Reporter $decoratedReporter,
        ?int $numberOfShownMutations,
        string $expected,
    ): void {
        $output = new BufferedOutput();

        $reporter = new FileLocationReporter(
            $decoratedReporter,
            $output,
            $numberOfShownMutations,
        );

        $reporter->report();

        $actual = $output->fetch();

        $this->assertSame(
            $expected,
            Str::toUnixLineEndings($actual),
        );
    }

    public static function reporterProvider(): iterable
    {
        yield 'no file reporter when all the mutations are shown' => [
            new FakeReporter(),
            null,
            <<<'EOF'

                EOF,
        ];

        yield 'no file reporter when no mutations are shown' => [
            new FakeReporter(),
            0,
            <<<'EOF'

                Note: to see escaped mutants run Infection with "--show-mutations=20" or configure file reporters.

                EOF,
        ];

        yield 'no file reporter when some mutations are shown' => [
            new FakeReporter(),
            10,
            <<<'EOF'

                EOF,
        ];

        yield 'one file reporter when all the mutations are shown' => [
            new FileReporter(
                '/path/to/report.txt',
                new FakeFileSystem(),
                new DummyLineMutationTestingResultsReporter([]),
                new FakeLogger(),
            ),
            null,
            <<<'EOF'

                Generated Reports:
                         - /path/to/report.txt

                EOF,
        ];

        yield 'one file reporter when no mutations are shown' => [
            new FileReporter(
                '/path/to/report.txt',
                new FakeFileSystem(),
                new DummyLineMutationTestingResultsReporter([]),
                new FakeLogger(),
            ),
            0,
            <<<'EOF'

                Generated Reports:
                         - /path/to/report.txt

                EOF,
        ];

        yield 'one file reporter when some mutations are shown' => [
            new FileReporter(
                '/path/to/report.txt',
                new FakeFileSystem(),
                new DummyLineMutationTestingResultsReporter([]),
                new FakeLogger(),
            ),
            10,
            <<<'EOF'

                Generated Reports:
                         - /path/to/report.txt

                EOF,
        ];

        yield 'one file reporter with a relative path' => [
            new FileReporter(
                'relative-path/to/report.txt',
                new FakeFileSystem(),
                new DummyLineMutationTestingResultsReporter([]),
                new FakeLogger(),
            ),
            0,
            <<<'EOF'

                Generated Reports:
                         - relative-path/to/report.txt

                EOF,
        ];

        yield 'nominal' => [
            new FederatedReporter(
                new FederatedReporter(
                    new FileReporter(
                        'report1.txt',
                        new FakeFileSystem(),
                        new DummyLineMutationTestingResultsReporter([]),
                        new FakeLogger(),
                    ),
                    new FileReporter(
                        'report2.txt',
                        new FakeFileSystem(),
                        new DummyLineMutationTestingResultsReporter([]),
                        new FakeLogger(),
                    ),
                    new FakeReporter(),
                ),
                new FileReporter(
                    'report3.txt',
                    new FakeFileSystem(),
                    new DummyLineMutationTestingResultsReporter([]),
                    new FakeLogger(),
                ),
                new FakeReporter(),
            ),
            null,
            <<<'EOF'

                Generated Reports:
                         - report1.txt
                         - report2.txt
                         - report3.txt

                EOF,
        ];
    }
}
