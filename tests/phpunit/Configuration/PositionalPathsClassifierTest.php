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

use Infection\Configuration\ClassifiedPaths;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\Mago;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\PositionalPathsClassifier;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\FileSystem\FileSystem;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function str_starts_with;

#[CoversClass(PositionalPathsClassifier::class)]
#[CoversClass(ClassifiedPaths::class)]
#[Group('integration')]
final class PositionalPathsClassifierTest extends TestCase
{
    public function test_it_returns_empty_buckets_when_paths_is_empty(): void
    {
        $classified = PositionalPathsClassifier::fromPaths([], $this->createSchema(['src']), $this->acceptingFileSystem());

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame([], $classified->testPaths);
    }

    public function test_it_routes_single_source_path(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['src/SomeFile.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame(['src/SomeFile.php'], $classified->sourcePaths);
        $this->assertSame([], $classified->testPaths);
    }

    public function test_it_routes_single_test_path(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['tests/SomeTest.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame(['tests/SomeTest.php'], $classified->testPaths);
    }

    public function test_it_routes_multiple_source_paths(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['src/A.php', 'src/B.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame(['src/A.php', 'src/B.php'], $classified->sourcePaths);
        $this->assertSame([], $classified->testPaths);
    }

    public function test_it_routes_multiple_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['tests/Unit/A', 'tests/Unit/B'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame(['tests/Unit/A', 'tests/Unit/B'], $classified->testPaths);
    }

    public function test_it_routes_mixed_source_and_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['src/A.php', 'tests/ATest.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame(['src/A.php'], $classified->sourcePaths);
        $this->assertSame(['tests/ATest.php'], $classified->testPaths);
    }

    public function test_it_accepts_any_order_of_source_and_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['tests/ATest.php', 'src/A.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame(['src/A.php'], $classified->sourcePaths);
        $this->assertSame(['tests/ATest.php'], $classified->testPaths);
    }

    public function test_it_routes_multiple_sources_and_multiple_tests(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['src/A.php', 'tests/ATest.php', 'src/B.php', 'tests/BTest.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame(['src/A.php', 'src/B.php'], $classified->sourcePaths);
        $this->assertSame(['tests/ATest.php', 'tests/BTest.php'], $classified->testPaths);
    }

    /**
     * TODO this should be classified as test, because test can leave in src folders
     */
    public function test_it_classifies_paths_containing_tests_segment_as_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['lib/gocardless/tests/Unit/FooTest.php'],
            $this->createSchema(['src', 'lib']),
            $this->fileSystemWith([
                '/project/lib/gocardless/tests/Unit/FooTest.php',
            ]),
        );

        // TODO to be fixed in other PR
        // $this->assertSame([], $classified->sourcePaths);
        // $this->assertSame(['lib/gocardless/tests/Unit/FooTest.php'], $classified->testPaths);

        $this->assertSame([], $classified->testPaths);
        $this->assertSame(['lib/gocardless/tests/Unit/FooTest.php'], $classified->sourcePaths);
    }

    public function test_it_classifies_capitalized_tests_directory_as_test_path(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['Tests/Unit/FooTest.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame(['Tests/Unit/FooTest.php'], $classified->testPaths);
    }

    public function test_it_classifies_capitalized_tests_root_as_test_path(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['Tests/'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame(['Tests/'], $classified->testPaths);
    }

    public function test_it_does_not_classify_digit_starting_name_as_bare_source_filter(): void
    {
        // "1Foo.php" does not match the Pascal-case heuristic (digits are excluded),
        // so it must exist on disk and be classified via directory lookup.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path argument "1Foo.php": multiple paths must be passed as separate arguments.');

        PositionalPathsClassifier::fromPaths(
            ['1Foo.php'],
            $this->createSchema(['src']),
            $this->fileSystemWith([]),
        );
    }

    public function test_it_does_not_treat_non_tests_word_fragments_as_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['src/contest/Foo.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame(['src/contest/Foo.php'], $classified->sourcePaths);
        $this->assertSame([], $classified->testPaths);
    }

    public function test_it_routes_bare_source_filter_with_bare_test_filename(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['Plus.php', 'PlusTest.php'],
            $this->createSchema(['src']),
            $this->fileSystemWith([
                '/project/PlusTest.php',
            ]),
        );

        $this->assertSame(['Plus.php'], $classified->sourcePaths);
        $this->assertSame(['PlusTest.php'], $classified->testPaths);
    }

    public function test_it_supports_multiple_configured_source_directories(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['lib/B.php', 'tests/CTest.php'],
            $this->createSchema(['src', 'lib']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame(['lib/B.php'], $classified->sourcePaths);
        $this->assertSame(['tests/CTest.php'], $classified->testPaths);
    }

    public function test_it_treats_everything_as_test_path_when_no_source_directories_configured(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['src/SomeFile.php'],
            $this->createSchema([]),
            $this->acceptingFileSystem(),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame(['src/SomeFile.php'], $classified->testPaths);
    }

    public function test_it_classifies_paths_under_singular_test_directory_as_test_paths(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['test/Foo.php'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame(['test/Foo.php'], $classified->testPaths);
    }

    public function test_it_rejects_fqcn_with_leading_backslash(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FQCN-style arguments like "\App\Foo" are not yet supported. See https://github.com/infection/infection/issues/2237.');

        PositionalPathsClassifier::fromPaths(
            ['\App\Foo'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );
    }

    public function test_it_rejects_fqcn_with_method_coordinate_separator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FQCN-style arguments like "App\Foo::method" are not yet supported.');

        PositionalPathsClassifier::fromPaths(
            ['App\Foo::method'],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );
    }

    public function test_it_rejects_existing_class_name_via_class_exists_check(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FQCN-style arguments like "Infection\Configuration\PositionalPathsClassifier" are not yet supported.');

        PositionalPathsClassifier::fromPaths(
            [PositionalPathsClassifier::class],
            $this->createSchema(['src']),
            $this->acceptingFileSystem(),
        );
    }

    public function test_it_rejects_path_shaped_arguments_that_do_not_exist_on_disk(): void
    {
        // Catches typos like "scr/Foo.php" (a slip of "src/") at the classifier
        // layer instead of letting them silently route to PHPUnit and surface
        // as confusing test-framework errors downstream.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path argument "scr/Foo.php": multiple paths must be passed as separate arguments.');

        PositionalPathsClassifier::fromPaths(
            ['scr/Foo.php'],
            $this->createSchema(['src']),
            $this->fileSystemWith([]),
        );
    }

    public function test_it_classifies_an_existing_file_inside_source_directories_as_source(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['src/SomeFile.php'],
            $this->createSchema(['src']),
            $this->fileSystemWith(['/project/src/SomeFile.php']),
        );

        $this->assertSame(['src/SomeFile.php'], $classified->sourcePaths);
        $this->assertSame([], $classified->testPaths);
    }

    public function test_it_classifies_an_existing_directory_inside_source_directories_as_source(): void
    {
        $classified = PositionalPathsClassifier::fromPaths(
            ['src/SubTree'],
            $this->createSchema(['src']),
            $this->fileSystemWith(['/project/src/SubTree']),
        );

        $this->assertSame(['src/SubTree'], $classified->sourcePaths);
        $this->assertSame([], $classified->testPaths);
    }

    public function test_it_classifies_an_existing_path_outside_source_directories_as_test(): void
    {
        // Real path on disk that isn't under any configured source directory:
        // routed to the test-framework slot. This is what enables
        // `infection run tests/SomeFolder` to behave like `phpunit tests/SomeFolder`.
        $classified = PositionalPathsClassifier::fromPaths(
            ['integration/SomeFolder'],
            $this->createSchema(['src']),
            $this->fileSystemWith(['/project/integration/SomeFolder']),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame(['integration/SomeFolder'], $classified->testPaths);
    }

    /**
     * @param list<non-empty-string> $paths
     * @param list<non-empty-string> $expectedSourcePaths
     */
    #[DataProvider('provideSingleArgumentSourceCases')]
    public function test_cli_case_source_values_are_routed_to_source(
        array $paths,
        array $expectedSourcePaths,
    ): void {
        $classified = PositionalPathsClassifier::fromPaths(
            $paths,
            $this->createSchema(['src']),
            $this->fileSystemWith([
                '/project/src/Service/Mailer.php',
                '/project/src/Entity/Foobar.php',
            ]),
        );

        $this->assertSame($expectedSourcePaths, $classified->sourcePaths);
        $this->assertSame([], $classified->testPaths);
    }

    /**
     * @return iterable<string, array{0: list<non-empty-string>, 1: list<non-empty-string>}>
     */
    public static function provideSingleArgumentSourceCases(): iterable
    {
        yield 'symbolic source token Plus_' => [
            ['Plus_'],
            ['Plus_'],
        ];

        yield 'symbolic php token Plus_.php' => [
            ['Plus_.php'],
            ['Plus_.php'],
        ];

        yield 'source file path' => [
            ['src/Service/Mailer.php'],
            ['src/Service/Mailer.php'],
        ];

        yield 'source directory path' => [
            ['src/Service/'],
            ['src/Service/'],
        ];

        yield 'multiple source file paths' => [
            ['src/Service/Mailer.php', 'src/Entity/Foobar.php'],
            ['src/Service/Mailer.php', 'src/Entity/Foobar.php'],
        ];

        yield 'multiple symbolic source values' => [
            ['Mailer.php', 'Foobar.php'],
            ['Mailer.php', 'Foobar.php'],
        ];
    }

    /**
     * @param list<non-empty-string> $paths
     * @param list<non-empty-string> $expectedTestPaths
     */
    #[DataProvider('provideSingleArgumentTestCases')]
    public function test_cli_case_test_values_are_routed_to_test(
        array $paths,
        array $expectedTestPaths,
    ): void {
        $classified = PositionalPathsClassifier::fromPaths(
            $paths,
            $this->createSchema(['src']),
            $this->fileSystemWith([
                '/project/tests/Unit/Service/MailerTest.php',
                '/project/tests/Unit/MailerTest.php',
                '/project/tests/Unit/FooTest.php',
                '/project/tests/Integration/MailerTest.php',
                '/project/Tests/Unit/FooTest.php',
                '/project/Tests/Integration/Service/MailerTest.php',
            ]),
        );

        $this->assertSame([], $classified->sourcePaths);
        $this->assertSame($expectedTestPaths, $classified->testPaths);
    }

    /**
     * @return iterable<string, array{0: list<non-empty-string>, 1: list<non-empty-string>}>
     */
    public static function provideSingleArgumentTestCases(): iterable
    {
        yield 'test file path' => [
            ['tests/Unit/Service/MailerTest.php'],
            ['tests/Unit/Service/MailerTest.php'],
        ];

        yield 'test directory path with service segment' => [
            ['tests/Unit/Service/'],
            ['tests/Unit/Service/'],
        ];

        yield 'test directory unit segment' => [
            ['tests/Unit/'],
            ['tests/Unit/'],
        ];

        yield 'test directory with trailing slash' => [
            ['tests/'],
            ['tests/'],
        ];

        yield 'test directory token without slash' => [
            ['tests'],
            ['tests'],
        ];

        yield 'multiple test directories' => [
            ['tests/Unit/', 'tests/Integration/'],
            ['tests/Unit/', 'tests/Integration/'],
        ];

        yield 'multiple test files' => [
            ['tests/Unit/MailerTest.php', 'tests/Unit/FooTest.php'],
            ['tests/Unit/MailerTest.php', 'tests/Unit/FooTest.php'],
        ];

        yield 'capitalized Tests root directory' => [
            ['Tests'],
            ['Tests'],
        ];

        yield 'capitalized Tests directory with slash' => [
            ['Tests/Unit/'],
            ['Tests/Unit/'],
        ];
    }

    /**
     * @param list<non-empty-string> $paths
     * @param list<non-empty-string> $expectedSourcePaths
     * @param list<non-empty-string> $expectedTestPaths
     */
    #[DataProvider('provideMixedSourceAndTestCases')]
    public function test_cli_case_mixed_source_and_test_paths_are_classified_correctly(
        array $paths,
        array $expectedSourcePaths,
        array $expectedTestPaths,
    ): void {
        $classified = PositionalPathsClassifier::fromPaths(
            $paths,
            $this->createSchema(['src']),
            $this->fileSystemWith([
                '/project/src/A.php',
                '/project/src/B.php',
                '/project/src/Service/Mailer.php',
                '/project/src/Entity/Foobar.php',
                '/project/tests/ATest.php',
                '/project/tests/BTest.php',
                '/project/tests/Unit/Service/MailerTest.php',
                '/project/tests/Integration/Service/MailerTest.php',
            ]),
        );

        $this->assertSame($expectedSourcePaths, $classified->sourcePaths);
        $this->assertSame($expectedTestPaths, $classified->testPaths);
    }

    /**
     * @return iterable<string, array{0: list<non-empty-string>, 1: list<non-empty-string>, 2: list<non-empty-string>}>
     */
    public static function provideMixedSourceAndTestCases(): iterable
    {
        yield 'source file + test file' => [
            ['src/Service/Mailer.php', 'tests/Unit/Service/MailerTest.php'],
            ['src/Service/Mailer.php'],
            ['tests/Unit/Service/MailerTest.php'],
        ];

        yield 'source file + test folder' => [
            ['src/Service/Mailer.php', 'tests/Unit/Service/'],
            ['src/Service/Mailer.php'],
            ['tests/Unit/Service/'],
        ];

        yield 'source folder + test file' => [
            ['src/Service/', 'tests/Unit/Service/MailerTest.php'],
            ['src/Service/'],
            ['tests/Unit/Service/MailerTest.php'],
        ];

        yield 'source folder + test folder' => [
            ['src/Service/', 'tests/Unit/Service/'],
            ['src/Service/'],
            ['tests/Unit/Service/'],
        ];

        yield 'symbolic php source + test folder' => [
            ['Mailer.php', 'tests/Unit/Service/'],
            ['Mailer.php'],
            ['tests/Unit/Service/'],
        ];

        yield 'symbolic source + test folder' => [
            ['Mailer', 'tests/Unit/Service/'],
            ['Mailer'],
            ['tests/Unit/Service/'],
        ];

        yield 'multiple symbolic sources + test folder' => [
            ['Mailer', 'Plus_', 'tests/Unit/Service/'],
            ['Mailer', 'Plus_'],
            ['tests/Unit/Service/'],
        ];

        yield 'test file + source file (reversed order)' => [
            ['tests/Unit/Service/MailerTest.php', 'src/Service/Mailer.php'],
            ['src/Service/Mailer.php'],
            ['tests/Unit/Service/MailerTest.php'],
        ];

        yield 'multiple sources + multiple tests' => [
            ['src/A.php', 'tests/ATest.php', 'src/B.php', 'tests/BTest.php'],
            ['src/A.php', 'src/B.php'],
            ['tests/ATest.php', 'tests/BTest.php'],
        ];

        yield 'multiple source files + multiple test directories' => [
            ['src/Service/Mailer.php', 'src/Entity/Foobar.php', 'tests/Unit/Service/', 'tests/Integration/'],
            ['src/Service/Mailer.php', 'src/Entity/Foobar.php'],
            ['tests/Unit/Service/', 'tests/Integration/'],
        ];

        yield 'test folder + symbolic php source (reversed order)' => [
            ['tests/Unit/Service/', 'Mailer.php'],
            ['Mailer.php'],
            ['tests/Unit/Service/'],
        ];

        yield 'test folder + symbolic source (reversed order)' => [
            ['tests/Unit/Service/', 'Mailer'],
            ['Mailer'],
            ['tests/Unit/Service/'],
        ];

        yield 'test folder + multiple symbolic sources (reversed order)' => [
            ['tests/Unit/Service/', 'Mailer', 'Plus_'],
            ['Mailer', 'Plus_'],
            ['tests/Unit/Service/'],
        ];
    }

    private function acceptingFileSystem(): FileSystem
    {
        $fileSystem = $this->createMock(FileSystem::class);
        $fileSystem->method('isReadableFile')->willReturn(true);
        $fileSystem->method('isReadableDirectory')->willReturn(true);

        return $fileSystem;
    }

    /**
     * @param list<non-empty-string> $existingPaths Absolute paths the fake should report as existing.
     */
    private function fileSystemWith(array $existingPaths): FileSystem
    {
        $fileSystem = $this->createMock(FileSystem::class);

        $isExistingPath = static function (string $filename) use ($existingPaths): bool {
            foreach ($existingPaths as $existingPath) {
                if ($existingPath === $filename || str_starts_with($existingPath, $filename)) {
                    return true;
                }
            }

            return false;
        };

        $fileSystem->method('isReadableFile')->willReturnCallback($isExistingPath);
        $fileSystem->method('isReadableDirectory')->willReturnCallback($isExistingPath);

        return $fileSystem;
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
