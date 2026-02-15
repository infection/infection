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

use function array_shift;
use function explode;
use Infection\Differ\DiffColorizer;
use Infection\Framework\Str;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Reporter\Reporter;
use Infection\Reporter\ShowMutationsReporter;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use function sprintf;
use Symfony\Component\Console\Output\BufferedOutput;
use function ucfirst;

#[CoversClass(ShowMutationsReporter::class)]
final class ShowMutationsReporterTest extends TestCase
{
    private BufferedOutput $output;

    private ResultsCollector $resultsCollector;

    private DiffColorizer $diffColorizer;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();

        $this->resultsCollector = new ResultsCollector();
        $this->diffColorizer = $this->createDiffColorizerMock();
    }

    /**
     * @param MutantExecutionResult[] $results
     */
    #[DataProvider('resultsProvider')]
    public function test_it_show_the_mutations(
        array $results,
        ?int $numberOfShownMutations,
        bool $withUncovered,
        bool $withTimeouts,
        string $expected,
    ): void {
        $this->resultsCollector->collect(...$results);

        $reporter = $this->createReporter(
            numberOfShownMutations: $numberOfShownMutations,
            withUncovered: $withUncovered,
            withTimeouts: $withTimeouts,
        );
        $reporter->report();

        $actual = Str::toUnixLineEndings($this->output->fetch());

        $this->assertSame($expected, $actual);
    }

    public static function resultsProvider(): iterable
    {
        yield 'no results' => [
            [],
            10,
            false,
            false,
            <<<'DISPLAY'

                DISPLAY,
        ];

        yield 'all statuses of mutations' => [
            self::createResultForStatuses('/path/to/file1.php', 2),
            10,
            false,
            false,
            <<<'DISPLAY'

                Escaped mutants:
                ================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                DISPLAY,
        ];

        yield 'all statuses of mutations with uncovered mutations shown' => [
            self::createResultForStatuses('/path/to/file1.php', 2),
            10,
            true,
            false,
            <<<'DISPLAY'

                Escaped mutants:
                ================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                Not covered mutants:
                ====================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] notCoveredMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] notCoveredMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                DISPLAY,
        ];

        yield 'all statuses of mutations with timeouts mutations shown' => [
            self::createResultForStatuses('/path/to/file1.php', 2),
            10,
            false,
            true,
            <<<'DISPLAY'

                Escaped mutants:
                ================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                Timed out mutants:
                ==================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] timedOutMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] timedOutMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                DISPLAY,
        ];

        yield 'all statuses of mutations with uncovered & timeouts mutations shown' => [
            self::createResultForStatuses('/path/to/file1.php', 2),
            10,
            true,
            true,
            <<<'DISPLAY'

                Escaped mutants:
                ================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                Not covered mutants:
                ====================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] notCoveredMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] notCoveredMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                Timed out mutants:
                ==================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] timedOutMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] timedOutMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                DISPLAY,
        ];

        yield 'all statuses of mutations with uncovered & timeouts mutations shown with more mutations than the limit' => [
            self::createResultForStatuses('/path/to/file1.php', 2),
            1,
            true,
            true,
            <<<'DISPLAY'

                Escaped mutants:
                ================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                ... and 1 more mutants were omitted. Use "--show-mutations=max" to see all of them.

                DISPLAY,
        ];

        yield 'all statuses of mutations with uncovered & timeouts mutations shown with limit=0' => [
            self::createResultForStatuses('/path/to/file1.php', 2),
            0,
            true,
            true,
            <<<'DISPLAY'

                DISPLAY,
        ];

        yield 'all mutations shown with unlimited budget (null)' => [
            self::createResultForStatuses('/path/to/file1.php', 3),
            null,
            true,
            true,
            <<<'DISPLAY'

                Escaped mutants:
                ================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                3) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation2
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                Not covered mutants:
                ====================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] notCoveredMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] notCoveredMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                3) /path/to/file1.php:10    [M] CustomMutator [ID] notCoveredMutation2
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                Timed out mutants:
                ==================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] timedOutMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] timedOutMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                3) /path/to/file1.php:10    [M] CustomMutator [ID] timedOutMutation2
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                DISPLAY,
        ];

        yield 'budget exhausted in second section' => [
            self::createResultForStatuses('/path/to/file1.php', 2),
            3,
            true,
            false,
            <<<'DISPLAY'

                Escaped mutants:
                ================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                Not covered mutants:
                ====================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] notCoveredMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                ... and 1 more mutants were omitted. Use "--show-mutations=max" to see all of them.

                DISPLAY,
        ];

        yield 'correct omitted count with multiple mutations in single section' => [
            self::createResultForStatuses('/path/to/file1.php', 5),
            2,
            false,
            false,
            <<<'DISPLAY'

                Escaped mutants:
                ================


                1) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation0
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                2) /path/to/file1.php:10    [M] CustomMutator [ID] escapedMutation1
                colorized(
                    --- Original
                    +++ Mutated
                    @@ @@
                    -$a = 1;
                    +$a = 2;
                )

                ... and 3 more mutants were omitted. Use "--show-mutations=max" to see all of them.

                DISPLAY,
        ];
    }

    private function createReporter(
        ?int $numberOfShownMutations,
        bool $withUncovered,
        bool $withTimeouts,
    ): Reporter {
        return new ShowMutationsReporter(
            $this->output,
            $this->resultsCollector,
            $this->diffColorizer,
            $numberOfShownMutations,
            $withUncovered,
            $withTimeouts,
        );
    }

    private static function createMutationExecutionResult(
        string $sourceFilePath,
        string $mutationHash,
        DetectionStatus $status,
    ): MutantExecutionResult {
        return MutantExecutionResultBuilder::withMinimalTestData()
            ->withOriginalFilePath($sourceFilePath)
            ->withMutatorName('CustomMutator')
            ->withMutantHash($mutationHash)
            ->withDetectionStatus($status)
            ->build();
    }

    /**
     * @param positive-int $countPerStatus
     *
     * @return list<MutantExecutionResult>
     */
    private static function createResultForStatuses(
        string $sourceFilePath,
        int $countPerStatus,
        DetectionStatus ...$excluded,
    ): array {
        $results = self::generateResultForStatuses(
            $sourceFilePath,
            $countPerStatus,
            ...$excluded,
        );

        return take($results)->toList();
    }

    /**
     * @param positive-int $countPerStatus
     *
     * @return iterable<MutantExecutionResult>
     */
    private static function generateResultForStatuses(
        string $sourceFilePath,
        int $countPerStatus,
        DetectionStatus ...$excluded,
    ): iterable {
        $statuses = DetectionStatus::getCasesExcluding(...$excluded);

        foreach ($statuses as $status) {
            for ($i = 0; $i < $countPerStatus; ++$i) {
                yield $status => self::createMutationExecutionResult(
                    $sourceFilePath,
                    sprintf(
                        '%sMutation%s',
                        self::getCamelCaseStatusName($status),
                        $i,
                    ),
                    $status,
                );
            }
        }
    }

    private static function getCamelCaseStatusName(DetectionStatus $status): string
    {
        $parts = explode(' ', $status->value);

        $firstPart = array_shift($parts);
        $name = $firstPart;

        foreach ($parts as $part) {
            $name .= ucfirst($part);
        }

        return $name;
    }

    private function createDiffColorizerMock(): DiffColorizer
    {
        $diffColorizerMock = $this->createMock(DiffColorizer::class);
        $diffColorizerMock
            ->method('colorize')
            ->willReturnCallback(
                static fn (string $diff): string => sprintf(
                    'colorized(%s%s%s)',
                    "\n",
                    Str::indent($diff, '    '),
                    "\n",
                ),
            );

        return $diffColorizerMock;
    }
}
