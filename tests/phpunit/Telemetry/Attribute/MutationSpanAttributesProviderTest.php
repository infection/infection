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

namespace Infection\Tests\Telemetry\Attribute;

use Infection\Mutant\DetectionStatus;
use Infection\Telemetry\Attribute\MutationSpanAttributesProvider;
use Infection\Telemetry\ProjectRelativePathResolver;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutationSpanAttributesProvider::class)]
#[CoversClass(ProjectRelativePathResolver::class)]
final class MutationSpanAttributesProviderTest extends TestCase
{
    public function test_it_provides_mutation_identity_and_location_attributes(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withProjectDirectory('/path/to/project')
            ->build();
        $provider = new MutationSpanAttributesProvider(
            new ProjectRelativePathResolver($configuration),
            $configuration->timeoutsAsEscaped,
        );
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->withOriginalFilePath('/path/to/project/src/Foo.php')
            ->withMutatorName('For_')
            ->build();

        $this->assertSame(
            [
                'infection.mutation.id' => 'mutation-A',
                'infection.mutator.name' => 'For_',
                'code.file.path' => 'src/Foo.php',
                'code.line.start' => 10,
                'code.line.end' => 15,
            ],
            $provider->provide($mutation),
        );
    }

    public function test_it_keeps_relative_file_paths_unchanged(): void
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withProjectDirectory('/path/to/project')
            ->build();
        $provider = new MutationSpanAttributesProvider(
            new ProjectRelativePathResolver($configuration),
            $configuration->timeoutsAsEscaped,
        );

        $sourceFileRelativePath = 'src/Foo.php';
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->withOriginalFilePath($sourceFileRelativePath)
            ->build();

        $actual = $provider->provide($mutation)['code.file.path'];

        $this->assertSame($sourceFileRelativePath, $actual);
    }

    #[DataProvider('msiCategoryProvider')]
    public function test_it_provides_the_msi_category(
        DetectionStatus $detectionStatus,
        bool $timeoutsAsEscaped,
        string $expectedCategory,
    ): void {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withTimeoutsAsEscaped($timeoutsAsEscaped)
            ->build();
        $provider = new MutationSpanAttributesProvider(
            new ProjectRelativePathResolver($configuration),
            $configuration->timeoutsAsEscaped,
        );

        $this->assertSame(
            ['infection.mutation.msi.category' => $expectedCategory],
            $provider->provideResultAttributes($detectionStatus),
        );
    }

    /**
     * @return iterable<string, array{DetectionStatus, bool, string}>
     */
    public static function msiCategoryProvider(): iterable
    {
        yield 'killed by tests' => [DetectionStatus::KILLED_BY_TESTS, false, 'covered'];

        yield 'killed by static analysis' => [DetectionStatus::KILLED_BY_STATIC_ANALYSIS, false, 'covered'];

        yield 'error' => [DetectionStatus::ERROR, false, 'covered'];

        yield 'syntax error' => [DetectionStatus::SYNTAX_ERROR, false, 'covered'];

        yield 'timed out counted as covered' => [DetectionStatus::TIMED_OUT, false, 'covered'];

        yield 'timed out counted as not covered' => [DetectionStatus::TIMED_OUT, true, 'not_covered'];

        yield 'escaped' => [DetectionStatus::ESCAPED, false, 'not_covered'];

        yield 'not covered' => [DetectionStatus::NOT_COVERED, false, 'not_covered'];

        yield 'skipped' => [DetectionStatus::SKIPPED, false, 'ineligible'];

        yield 'ignored' => [DetectionStatus::IGNORED, false, 'ineligible'];
    }
}
