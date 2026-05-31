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

namespace Infection\Tests\Configuration;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mago;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\PositionalPathsClassifier;
use Infection\Configuration\Schema\SchemaConfiguration;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PositionalPathsClassifier::class)]
final class PositionalPathsClassifierTest extends TestCase
{
    public function test_it_returns_empty_buckets_when_both_slots_are_empty(): void
    {
        $classified = PositionalPathsClassifier::fromSlots([], [], $this->createSchema(['src']));

        $this->assertSame([], $classified->sourcePaths);
        $this->assertNull($classified->testPath);
    }

    public function test_it_routes_single_source_slot_into_source_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/SomeFile.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame(['src/SomeFile.php'], $classified->sourcePaths);
        $this->assertNull($classified->testPath);
    }

    public function test_it_routes_single_test_slot_into_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['tests/SomeTest.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame('tests/SomeTest.php', $classified->testPath);
    }

    public function test_it_classifies_paths_containing_tests_segment_as_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['lib/gocardless/tests/Unit/FooTest.php'],
            [],
            $this->createSchema(['src', 'lib']),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame('lib/gocardless/tests/Unit/FooTest.php', $classified->testPath);
    }

    public function test_it_classifies_test_like_paths_inside_source_directories_as_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/tests/Unit/FooTest.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame('src/tests/Unit/FooTest.php', $classified->testPath);
    }

    public function test_it_classifies_test_file_suffix_as_test_path_even_inside_source_directories(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/Unit/FooTest.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame('src/Unit/FooTest.php', $classified->testPath);
    }

    public function test_it_does_not_treat_non_tests_word_fragments_as_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/contest/Foo.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame(['src/contest/Foo.php'], $classified->sourcePaths);
        $this->assertNull($classified->testPath);
    }

    public function test_it_classifies_bare_test_filename_as_test_path(): void
    {
        // A user passing `infection run FooTest.php` expects the test routing,
        // mirroring `--filter='FooTest.php'` from the original ticket.
        $classified = PositionalPathsClassifier::fromSlots(
            ['FooTest.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame('FooTest.php', $classified->testPath);
    }

    public function test_it_routes_bare_source_filter_with_bare_test_filename_in_two_slots(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['Plus.php'],
            ['PlusTest.php'],
            $this->createSchema(['src']),
        );

        $this->assertSame(['Plus.php'], $classified->sourcePaths);
        $this->assertSame('PlusTest.php', $classified->testPath);
    }

    public function test_it_treats_symbolic_values_like_filter_values_as_source_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['Plus'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame(['Plus'], $classified->sourcePaths);
        $this->assertNull($classified->testPath);
    }

    public function test_it_treats_symbolic_php_values_like_filter_values_as_source_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['Plus.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame(['Plus.php'], $classified->sourcePaths);
        $this->assertNull($classified->testPath);
    }

    public function test_it_routes_a_source_slot_and_a_test_slot_in_canonical_order(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/Foo.php'],
            ['tests/FooTest.php'],
            $this->createSchema(['src']),
        );

        $this->assertSame(['src/Foo.php'], $classified->sourcePaths);
        $this->assertSame('tests/FooTest.php', $classified->testPath);
    }

    public function test_slot_order_is_interchangeable(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['tests/FooTest.php'],
            ['src/Foo.php'],
            $this->createSchema(['src']),
        );

        $this->assertSame(['src/Foo.php'], $classified->sourcePaths);
        $this->assertSame('tests/FooTest.php', $classified->testPath);
    }

    public function test_it_allows_combining_symbolic_filter_value_with_test_path_in_two_slots(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['Plus'],
            ['tests/FooTest.php'],
            $this->createSchema(['src']),
        );

        $this->assertSame(['Plus'], $classified->sourcePaths);
        $this->assertSame('tests/FooTest.php', $classified->testPath);
    }

    public function test_it_allows_combining_symbolic_php_filter_value_with_test_path_in_two_slots(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['Plus.php'],
            ['tests/phpunit/Mutator/Arithmetic/PlusTest.php'],
            $this->createSchema(['src']),
        );

        $this->assertSame(['Plus.php'], $classified->sourcePaths);
        $this->assertSame('tests/phpunit/Mutator/Arithmetic/PlusTest.php', $classified->testPath);
    }

    public function test_it_accepts_multiple_source_paths_via_comma_in_a_slot(): void
    {
        // Source paths follow --filter conventions: comma-separated lists are fine.
        // Test paths are NOT comma-separable (PHPUnit's filter takes a single path),
        // so the test slot has exactly one entry here.
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/A.php', 'src/B.php'],
            ['tests/ATest.php'],
            $this->createSchema(['src']),
        );

        $this->assertSame(['src/A.php', 'src/B.php'], $classified->sourcePaths);
        $this->assertSame('tests/ATest.php', $classified->testPath);
    }

    public function test_it_supports_multiple_configured_source_directories(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['lib/B.php'],
            ['tests/CTest.php'],
            $this->createSchema(['src', 'lib']),
        );

        $this->assertSame(['lib/B.php'], $classified->sourcePaths);
        $this->assertSame('tests/CTest.php', $classified->testPath);
    }

    public function test_it_treats_everything_as_test_path_when_no_source_directories_configured(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/SomeFile.php'],
            [],
            $this->createSchema([]),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame('src/SomeFile.php', $classified->testPath);
    }

    public function test_it_rejects_slot_mixing_source_and_test_paths(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "<path>" argument mixes source and test paths.');

        PositionalPathsClassifier::fromSlots(
            ['src/Foo.php', 'tests/FooTest.php'],
            [],
            $this->createSchema(['src']),
        );
    }

    public function test_it_rejects_slot_mixing_with_message_naming_the_offending_slot(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "<secondary-path>" argument mixes source and test paths.');

        PositionalPathsClassifier::fromSlots(
            ['src/Foo.php'],
            ['src/Bar.php', 'tests/BarTest.php'],
            $this->createSchema(['src']),
        );
    }

    public function test_it_rejects_two_slots_classifying_as_source(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Both positional arguments resolved to source paths. Combine same-kind source paths with commas in a single argument (e.g. "src/A.php,src/B.php")');

        PositionalPathsClassifier::fromSlots(
            ['src/A.php'],
            ['src/B.php'],
            $this->createSchema(['src']),
        );
    }

    public function test_it_rejects_two_slots_classifying_as_test(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Both positional arguments resolved to test paths. Pass a single test path');

        PositionalPathsClassifier::fromSlots(
            ['tests/ATest.php'],
            ['tests/BTest.php'],
            $this->createSchema(['src']),
        );
    }

    public function test_it_rejects_comma_separated_test_paths_within_a_slot(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "<path>" argument lists multiple test paths separated by commas. Test paths must be a single file or directory; comma-separated test paths are not supported.');

        // The first slot's two items both classify as test (outside source.directories).
        // Comma-separated test paths aren't a thing — PHPUnit's filter takes a single path.
        PositionalPathsClassifier::fromSlots(
            ['tests/A.php', 'tests/B.php'],
            [],
            $this->createSchema(['src']),
        );
    }

    public function test_it_rejects_comma_separated_test_paths_in_the_second_slot(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "<secondary-path>" argument lists multiple test paths separated by commas.');

        PositionalPathsClassifier::fromSlots(
            ['src/A.php'],
            ['tests/A.php', 'tests/B.php'],
            $this->createSchema(['src']),
        );
    }

    public function test_assert_no_conflict_rejects_source_paths_with_filter_option(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/Foo.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot pass source paths as positional arguments together with the "--filter" option.');

        $classified->assertNoConflictWithExplicitOptions(true, false);
    }

    public function test_assert_no_conflict_rejects_test_paths_with_test_framework_extra_args(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['tests/FooTest.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot pass test paths as positional arguments together with the "--test-framework-extra-args" option.');

        $classified->assertNoConflictWithExplicitOptions(false, true);
    }

    public function test_assert_no_conflict_passes_when_unrelated_options_are_provided(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['src/Foo.php'],
            [],
            $this->createSchema(['src']),
        );

        $classified->assertNoConflictWithExplicitOptions(false, true);

        $this->assertSame(['src/Foo.php'], $classified->sourcePaths);
        $this->assertNull($classified->testPath);
    }

    public function test_it_classifies_paths_under_singular_test_directory_as_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['test/Foo.php'],
            [],
            $this->createSchema(['src']),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame('test/Foo.php', $classified->testPath);
    }

    public function test_it_classifies_paths_containing_singular_test_segment_as_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromSlots(
            ['lib/vendor/test/Unit/Foo.php'],
            [],
            $this->createSchema(['src', 'lib']),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame('lib/vendor/test/Unit/Foo.php', $classified->testPath);
    }

    /**
     * @param list<non-empty-string> $sourceDirectories
     */
    private function createSchema(array $sourceDirectories): SchemaConfiguration
    {
        return new SchemaConfiguration(
            pathname: '/project/infection.json5',
            timeout: null,
            source: new Source($sourceDirectories, []),
            logs: Logs::createEmpty(),
            tmpDir: null,
            phpUnit: new PhpUnit(null, null),
            phpStan: new PhpStan(null, null),
            mago: new Mago(null, null),
            ignoreMsiWithNoMutations: null,
            minMsi: null,
            minCoveredMsi: null,
            timeoutsAsEscaped: null,
            maxTimeouts: null,
            mutators: [],
            testFramework: null,
            bootstrap: null,
            initialTestsPhpOptions: null,
            testFrameworkExtraOptions: null,
            testFrameworkExtraArgs: null,
            staticAnalysisToolOptions: null,
            threads: null,
            dotsPerRow: null,
            staticAnalysisTool: null,
        );
    }
}
