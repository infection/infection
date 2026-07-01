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

namespace Infection\Tests\Configuration\PositionalPathsClassifier;

use Infection\Configuration\ClassifiedPaths;
use Infection\Configuration\PositionalPathsClassifier;
use Infection\FileSystem\InMemoryFileSystem;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(PositionalPathsClassifier::class)]
#[CoversClass(ClassifiedPaths::class)]
#[Group('integration')]
final class PositionalPathsClassifierTest extends TestCase
{
    private InMemoryFileSystem $fileSystem;

    private PositionalPathsClassifier $classifier;

    protected function setUp(): void
    {
        $this->fileSystem = new InMemoryFileSystem();
        $this->classifier = new PositionalPathsClassifier($this->fileSystem);
    }

    #[DataProvider('provideClassifiablePaths')]
    public function test_it_classifies_paths(Scenario $scenario): void
    {
        $this->createExistingPaths($scenario);

        $actual = $this->classifier->classify(
            $scenario->paths,
            $scenario->schema,
        );

        $this->assertEquals(
            $scenario->expectedClassifiedPaths(),
            $actual,
        );
    }

    public static function provideClassifiablePaths(): iterable
    {
        $baseScenario = Scenario::empty()->withSourceDirectories(['src']);

        yield 'no paths' => [$baseScenario];

        yield 'single source file path' => [
            $baseScenario
                ->withPaths(['src/SomeFile.php'])
                ->withExistingFiles(['/project/src/SomeFile.php'])
                ->withExpected(new ClassifiedPaths(['src/SomeFile.php'], [])),
        ];

        yield 'single test file path' => [
            $baseScenario
                ->withPaths(['tests/SomeTest.php'])
                ->withExistingFiles(['/project/tests/SomeTest.php'])
                ->withExpected(new ClassifiedPaths([], ['tests/SomeTest.php'])),
        ];

        yield 'multiple source file paths' => [
            $baseScenario
                ->withPaths(['src/A.php', 'src/B.php'])
                ->withExistingFiles(['/project/src/A.php', '/project/src/B.php'])
                ->withExpected(new ClassifiedPaths(['src/A.php', 'src/B.php'], [])),
        ];

        yield 'multiple test directories without trailing slashes' => [
            $baseScenario
                ->withPaths(['tests/Unit/A', 'tests/Unit/B'])
                ->withExistingFiles(['/project/tests/Unit/A/Test.php', '/project/tests/Unit/B/Test.php'])
                ->withExpected(new ClassifiedPaths([], ['tests/Unit/A', 'tests/Unit/B'])),
        ];

        yield 'mixed source and test paths' => [
            $baseScenario
                ->withPaths(['src/A.php', 'tests/ATest.php'])
                ->withExistingFiles(['/project/src/A.php', '/project/tests/ATest.php'])
                ->withExpected(new ClassifiedPaths(['src/A.php'], ['tests/ATest.php'])),
        ];

        yield 'mixed source and test paths in reversed order' => [
            $baseScenario
                ->withPaths(['tests/ATest.php', 'src/A.php'])
                ->withExistingFiles(['/project/src/A.php', '/project/tests/ATest.php'])
                ->withExpected(new ClassifiedPaths(['src/A.php'], ['tests/ATest.php'])),
        ];

        yield 'multiple sources and multiple tests' => [
            $baseScenario
                ->withPaths(['src/A.php', 'tests/ATest.php', 'src/B.php', 'tests/BTest.php'])
                ->withExistingFiles(['/project/src/A.php', '/project/src/B.php', '/project/tests/ATest.php', '/project/tests/BTest.php'])
                ->withExpected(new ClassifiedPaths(['src/A.php', 'src/B.php'], ['tests/ATest.php', 'tests/BTest.php'])),
        ];

        // TODO this should be classified as test, because tests can live in source folders.
        yield 'path containing tests segment under configured source directory' => [
            $baseScenario
                ->withPaths(['lib/gocardless/tests/Unit/FooTest.php'])
                ->withSourceDirectories(['src', 'lib'])
                ->withExistingFiles(['/project/lib/gocardless/tests/Unit/FooTest.php'])
                ->withExpected(new ClassifiedPaths(['lib/gocardless/tests/Unit/FooTest.php'], [])),
        ];

        yield 'capitalized Tests file path' => [
            $baseScenario
                ->withPaths(['Tests/Unit/FooTest.php'])
                ->withExistingFiles(['/project/Tests/Unit/FooTest.php'])
                ->withExpected(new ClassifiedPaths([], ['Tests/Unit/FooTest.php'])),
        ];

        yield 'capitalized Tests root directory' => [
            $baseScenario
                ->withPaths(['Tests/'])
                ->withExistingFiles(['/project/Tests/Unit/FooTest.php'])
                ->withExpected(new ClassifiedPaths([], ['Tests/'])),
        ];

        yield 'capitalized Tests root directory without slash' => [
            $baseScenario
                ->withPaths(['Tests'])
                ->withExistingFiles(['/project/Tests/Unit/FooTest.php'])
                ->withExpected(new ClassifiedPaths([], ['Tests'])),
        ];

        yield 'non-tests word fragment inside source directory' => [
            $baseScenario
                ->withPaths(['src/contest/Foo.php'])
                ->withExistingFiles(['/project/src/contest/Foo.php'])
                ->withExpected(new ClassifiedPaths(['src/contest/Foo.php'], [])),
        ];

        yield 'bare source filter with existing bare test filename' => [
            $baseScenario
                ->withPaths(['Plus.php', 'PlusTest.php'])
                ->withExistingFiles(['/project/PlusTest.php'])
                ->withExpected(new ClassifiedPaths(['Plus.php'], ['PlusTest.php'])),
        ];

        yield 'multiple configured source directories' => [
            $baseScenario
                ->withPaths(['lib/B.php', 'tests/CTest.php'])
                ->withSourceDirectories(['src', 'lib'])
                ->withExistingFiles(['/project/lib/B.php', '/project/tests/CTest.php'])
                ->withExpected(new ClassifiedPaths(['lib/B.php'], ['tests/CTest.php'])),
        ];

        yield 'no configured source directories routes existing paths to test' => [
            $baseScenario
                ->withPaths(['src/SomeFile.php'])
                ->withSourceDirectories([])
                ->withExistingFiles(['/project/src/SomeFile.php'])
                ->withExpected(new ClassifiedPaths([], ['src/SomeFile.php'])),
        ];

        yield 'singular test directory file path' => [
            $baseScenario
                ->withPaths(['test/Foo.php'])
                ->withExistingFiles(['/project/test/Foo.php'])
                ->withExpected(new ClassifiedPaths([], ['test/Foo.php'])),
        ];

        yield 'existing source directory path' => [
            $baseScenario
                ->withPaths(['src/SubTree'])
                ->withExistingFiles(['/project/src/SubTree/File.php'])
                ->withExpected(new ClassifiedPaths(['src/SubTree'], [])),
        ];

        yield 'existing path outside source directories' => [
            $baseScenario
                ->withPaths(['integration/SomeFolder'])
                ->withExistingFiles(['/project/integration/SomeFolder/File.php'])
                ->withExpected(new ClassifiedPaths([], ['integration/SomeFolder'])),
        ];

        yield 'symbolic source token' => [
            $baseScenario
                ->withPaths(['Plus_'])
                ->withExpected(new ClassifiedPaths(['Plus_'], [])),
        ];

        yield 'symbolic php source token' => [
            $baseScenario
                ->withPaths(['Plus_.php'])
                ->withExpected(new ClassifiedPaths(['Plus_.php'], [])),
        ];

        yield 'source directory path with trailing slash' => [
            $baseScenario
                ->withPaths(['src/Service/'])
                ->withExistingFiles(['/project/src/Service/Mailer.php'])
                ->withExpected(new ClassifiedPaths(['src/Service/'], [])),
        ];

        yield 'multiple symbolic source values' => [
            $baseScenario
                ->withPaths(['Mailer.php', 'Foobar.php'])
                ->withExpected(new ClassifiedPaths(['Mailer.php', 'Foobar.php'], [])),
        ];

        yield 'test directory path with service segment' => [
            $baseScenario
                ->withPaths(['tests/Unit/Service/'])
                ->withExistingFiles(['/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths([], ['tests/Unit/Service/'])),
        ];

        yield 'test directory unit segment' => [
            $baseScenario
                ->withPaths(['tests/Unit/'])
                ->withExistingFiles(['/project/tests/Unit/MailerTest.php'])
                ->withExpected(new ClassifiedPaths([], ['tests/Unit/'])),
        ];

        yield 'multiple test directories with trailing slashes' => [
            $baseScenario
                ->withPaths(['tests/Unit/', 'tests/Integration/'])
                ->withExistingFiles(['/project/tests/Unit/MailerTest.php', '/project/tests/Integration/MailerTest.php'])
                ->withExpected(new ClassifiedPaths([], ['tests/Unit/', 'tests/Integration/'])),
        ];

        yield 'test directory with trailing slash' => [
            $baseScenario
                ->withPaths(['tests/'])
                ->withExistingFiles(['/project/tests/Unit/MailerTest.php'])
                ->withExpected(new ClassifiedPaths([], ['tests/'])),
        ];

        yield 'test directory token without slash' => [
            $baseScenario
                ->withPaths(['tests'])
                ->withExistingFiles(['/project/tests/Unit/MailerTest.php'])
                ->withExpected(new ClassifiedPaths([], ['tests'])),
        ];

        yield 'multiple test files' => [
            $baseScenario
                ->withPaths(['tests/Unit/MailerTest.php', 'tests/Unit/FooTest.php'])
                ->withExistingFiles(['/project/tests/Unit/MailerTest.php', '/project/tests/Unit/FooTest.php'])
                ->withExpected(new ClassifiedPaths([], ['tests/Unit/MailerTest.php', 'tests/Unit/FooTest.php'])),
        ];

        yield 'capitalized Tests directory with slash' => [
            $baseScenario
                ->withPaths(['Tests/Unit/'])
                ->withExistingFiles(['/project/Tests/Unit/FooTest.php'])
                ->withExpected(new ClassifiedPaths([], ['Tests/Unit/'])),
        ];

        yield 'source file and test folder' => [
            $baseScenario
                ->withPaths(['src/Service/Mailer.php', 'tests/Unit/Service/'])
                ->withExistingFiles(['/project/src/Service/Mailer.php', '/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['src/Service/Mailer.php'], ['tests/Unit/Service/'])),
        ];

        yield 'source folder and test file' => [
            $baseScenario
                ->withPaths(['src/Service/', 'tests/Unit/Service/MailerTest.php'])
                ->withExistingFiles(['/project/src/Service/Mailer.php', '/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['src/Service/'], ['tests/Unit/Service/MailerTest.php'])),
        ];

        yield 'source folder and test folder' => [
            $baseScenario
                ->withPaths(['src/Service/', 'tests/Unit/Service/'])
                ->withExistingFiles(['/project/src/Service/Mailer.php', '/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['src/Service/'], ['tests/Unit/Service/'])),
        ];

        yield 'symbolic php source and test folder' => [
            $baseScenario
                ->withPaths(['Mailer.php', 'tests/Unit/Service/'])
                ->withExistingFiles(['/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['Mailer.php'], ['tests/Unit/Service/'])),
        ];

        yield 'symbolic source and test folder' => [
            $baseScenario
                ->withPaths(['Mailer', 'tests/Unit/Service/'])
                ->withExistingFiles(['/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['Mailer'], ['tests/Unit/Service/'])),
        ];

        yield 'multiple symbolic sources and test folder' => [
            $baseScenario
                ->withPaths(['Mailer', 'Plus_', 'tests/Unit/Service/'])
                ->withExistingFiles(['/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['Mailer', 'Plus_'], ['tests/Unit/Service/'])),
        ];

        yield 'test folder and symbolic php source in reversed order' => [
            $baseScenario
                ->withPaths(['tests/Unit/Service/', 'Mailer.php'])
                ->withExistingFiles(['/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['Mailer.php'], ['tests/Unit/Service/'])),
        ];

        yield 'test folder and symbolic source in reversed order' => [
            $baseScenario
                ->withPaths(['tests/Unit/Service/', 'Mailer'])
                ->withExistingFiles(['/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['Mailer'], ['tests/Unit/Service/'])),
        ];

        yield 'test folder and multiple symbolic sources in reversed order' => [
            $baseScenario
                ->withPaths(['tests/Unit/Service/', 'Mailer', 'Plus_'])
                ->withExistingFiles(['/project/tests/Unit/Service/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['Mailer', 'Plus_'], ['tests/Unit/Service/'])),
        ];

        yield 'multiple source files and multiple test directories' => [
            $baseScenario
                ->withPaths(['src/Service/Mailer.php', 'src/Entity/Foobar.php', 'tests/Unit/Service/', 'tests/Integration/'])
                ->withExistingFiles(['/project/src/Service/Mailer.php', '/project/src/Entity/Foobar.php', '/project/tests/Unit/Service/MailerTest.php', '/project/tests/Integration/MailerTest.php'])
                ->withExpected(new ClassifiedPaths(['src/Service/Mailer.php', 'src/Entity/Foobar.php'], ['tests/Unit/Service/', 'tests/Integration/'])),
        ];
    }

    #[DataProvider('provideInvalidPaths')]
    public function test_it_rejects_invalid_paths(Scenario $scenario): void
    {
        $this->createExistingPaths($scenario);

        $this->expectExceptionObject($scenario->expectedException());

        $this->classifier->classify(
            $scenario->paths,
            $scenario->schema,
        );
    }

    public static function provideInvalidPaths(): iterable
    {
        $baseScenario = Scenario::empty();

        yield 'FQCN with leading backslash' => [
            $baseScenario
                ->withPaths(['\App\Foo'])
                ->withExpected(new InvalidArgumentException('FQCN-style arguments like "\App\Foo" are not yet supported. See https://github.com/infection/infection/issues/2237.')),
        ];

        yield 'FQCN with method coordinate separator' => [
            $baseScenario
                ->withPaths(['App\Foo::method'])
                ->withExpected(new InvalidArgumentException('FQCN-style arguments like "App\Foo::method" are not yet supported.')),
        ];

        yield 'existing class name' => [
            $baseScenario
                ->withPaths([PositionalPathsClassifier::class])
                ->withExpected(new InvalidArgumentException('FQCN-style arguments like "Infection\Configuration\PositionalPathsClassifier" are not yet supported.')),
        ];

        yield 'path-shaped argument that does not exist on disk' => [
            $baseScenario
                ->withPaths(['scr/Foo.php'])
                ->withExpected(new InvalidArgumentException('Invalid path argument "scr/Foo.php": multiple paths must be passed as separate arguments.')),
        ];

        yield 'digit-starting name' => [
            $baseScenario
                ->withPaths(['1Foo.php'])
                ->withExpected(new InvalidArgumentException('Invalid path argument "1Foo.php": multiple paths must be passed as separate arguments.')),
        ];
    }

    private function createExistingPaths(Scenario $scenario): void
    {
        foreach ($scenario->existingFiles as $existingFile) {
            $this->fileSystem->dumpFile($existingFile, 'content');
        }

        foreach ($scenario->existingDirectories as $existingDirectory) {
            $this->fileSystem->mkdir($existingDirectory);
        }
    }
}
