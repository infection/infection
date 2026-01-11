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

namespace Infection\Tests\TestFramework\Coverage\XmlReport;

use function dirname;
use Infection\FileSystem\FileSystem;
use Infection\TestFramework\Coverage\XmlReport\InvalidCoverage;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Path;
use ValueError;

#[Group('integration')]
#[CoversClass(SourceFileInfoProvider::class)]
final class SourceFileInfoProviderTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Fixtures';

    #[DataProvider('fileFixturesProvider')]
    public function test_it_provides_file_info_and_xpath(
        string $coverageIndexPath,
        string $coverageDir,
        string $relativeCoverageFilePath,
        string $projectSource,
        string $expectedSourceFilePath,
    ): void {
        // We configure the FileSystem so that the computed XML file needs to point to a real file
        // but the source file does not need to exist.
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->willReturn(true);
        $fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->willReturnCallback(file_get_contents(...));
        $fileSystemMock
            ->expects($this->once())
            ->method('realPath')
            ->with($expectedSourceFilePath)
            ->willReturn($expectedSourceFilePath);

        $provider = new SourceFileInfoProvider(
            $coverageIndexPath,
            $coverageDir,
            $relativeCoverageFilePath,
            $projectSource,
            $fileSystemMock,
        );

        // Note that in practice we care about the real path, not the pathname.
        // However, since we are creating a real SplFileInfo instance and the
        // path is a fake one, `getRealPath()` would return `false`.
        $actualSourceFilePath = $provider->provideFileInfo()->getPathname();

        $this->assertSame($expectedSourceFilePath, $actualSourceFilePath);

        // We cannot check that the XPath is correct... Only that we produced
        // a cached XPath.
        $xPath = $provider->provideXPath();
        $xPathAgain = $provider->provideXPath();

        $this->assertSame($xPath, $xPathAgain);
    }

    public function test_it_errors_when_the_xml_file_could_not_be_found(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->willReturn(false);

        $provider = new SourceFileInfoProvider(
            '/path/to/index.xml',
            '/path/to/coverage-dir',
            'zeroLevel.php.xml',
            'projectSource',
            $fileSystemMock,
        );

        $this->expectExceptionObject(
            new InvalidCoverage(
                'Could not find the XML coverage file "/path/to/coverage-dir/zeroLevel.php.xml" listed in "/path/to/index.xml". Make sure the coverage used is up to date',
            ),
        );

        $provider->provideFileInfo();
    }

    public function test_it_errors_when_the_source_file_could_not_be_found(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->with('/path/to/project/var/coverage-xml/src/zeroLevel.php.xml')
            ->willReturn(true);
        $fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->with('/path/to/project/var/coverage-xml/src/zeroLevel.php.xml')
            ->willReturn(
                <<<'XML'
                    <?xml version="1.0"?>
                    <phpunit xmlns="https://schema.phpunit.de/coverage/1.0">
                      <file name="Calculator.php" path="/Covered/Zero" hash="5166fd6f45f4b26afab9eaa7968e0c023bc35461">
                      </file>
                    </phpunit>
                    XML,
            );
        $fileSystemMock
            ->expects($this->once())
            ->method('realPath')
            ->with('/path/to/project/src/Covered/Zero/Calculator.php')
            ->willThrowException(new IOException(''));

        $provider = new SourceFileInfoProvider(
            '/path/to/project/var/coverage-xml/index.xml',
            '/path/to/project/var/coverage-xml',
            'src/zeroLevel.php.xml',
            '/path/to/project/src',
            $fileSystemMock,
        );

        $this->expectExceptionObject(
            new InvalidCoverage(
                'Could not find the source file "/path/to/project/src/Covered/Zero/Calculator.php" referred by "/path/to/project/var/coverage-xml/src/zeroLevel.php.xml". Make sure the coverage used is up to date',
            ),
        );

        $provider->provideFileInfo();
    }

    public function test_it_errors_when_the_xml_file_contains_invalid_xml(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->willReturn(true);
        $fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->willReturn('');

        $provider = new SourceFileInfoProvider(
            '/path/to/index.xml',
            '/path/to/coverage-dir',
            'zeroLevel.php.xml',
            'projectSource',
            $fileSystemMock,
        );

        // TODO: this is not ideal...
        $this->expectException(ValueError::class);

        $provider->provideFileInfo();
    }

    public static function fileFixturesProvider(): iterable
    {
        yield from DataProviderFactory::prefix(
            '[PHPUnit 09] ',
            self::phpUnit09InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 10] ',
            self::phpUnit10InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 11] ',
            self::phpUnit11InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.0] ',
            self::phpUnit120InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[PHPUnit 12.5] ',
            self::phpUnit125InfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[Codeception] ',
            self::codeceptionInfoProvider(),
        );

        yield from DataProviderFactory::prefix(
            '[phpspec] ',
            self::phpSpecInfoProvider(),
        );
    }

    private static function phpUnit09InfoProvider(): iterable
    {
        $phpunit9IndexPath = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-09/coverage-xml/index.xml');

        $createPhpUnit9Scenario = static fn (
            string $relativeCoverageFilePath,
            string $expected,
        ) => [
            $phpunit9IndexPath,
            dirname($phpunit9IndexPath),
            $relativeCoverageFilePath,
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src',
            $expected,
        ];

        yield 'covered class' => $createPhpUnit9Scenario(
            'Covered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Covered/Calculator.php',
        );

        yield 'covered trait' => $createPhpUnit9Scenario(
            'Covered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Covered/LoggerTrait.php',
        );

        yield 'covered class with trait' => $createPhpUnit9Scenario(
            'Covered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Covered/UserService.php',
        );

        yield 'covered function' => $createPhpUnit9Scenario(
            'Covered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Covered/functions.php',
        );

        yield 'uncovered class' => $createPhpUnit9Scenario(
            'Uncovered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Uncovered/Calculator.php',
        );

        yield 'uncovered trait' => $createPhpUnit9Scenario(
            'Uncovered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Uncovered/LoggerTrait.php',
        );

        yield 'uncovered class with trait' => $createPhpUnit9Scenario(
            'Uncovered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Uncovered/UserService.php',
        );

        yield 'uncovered function' => $createPhpUnit9Scenario(
            'Uncovered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_09-3/src/Uncovered/functions.php',
        );
    }

    private static function phpUnit10InfoProvider(): iterable
    {
        $phpunit10IndexPath = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-10/coverage-xml/index.xml');

        $createPhpUnit10Scenario = static fn (
            string $relativeCoverageFilePath,
            string $expected,
        ) => [
            $phpunit10IndexPath,
            dirname($phpunit10IndexPath),
            $relativeCoverageFilePath,
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src',
            $expected,
        ];

        yield 'covered class' => $createPhpUnit10Scenario(
            'Covered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Covered/Calculator.php',
        );

        yield 'covered trait' => $createPhpUnit10Scenario(
            'Covered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Covered/LoggerTrait.php',
        );

        yield 'covered class with trait' => $createPhpUnit10Scenario(
            'Covered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Covered/UserService.php',
        );

        yield 'covered function' => $createPhpUnit10Scenario(
            'Covered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Covered/functions.php',
        );

        yield 'uncovered class' => $createPhpUnit10Scenario(
            'Uncovered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Uncovered/Calculator.php',
        );

        yield 'uncovered trait' => $createPhpUnit10Scenario(
            'Uncovered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Uncovered/LoggerTrait.php',
        );

        yield 'uncovered class with trait' => $createPhpUnit10Scenario(
            'Uncovered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Uncovered/UserService.php',
        );

        yield 'uncovered function' => $createPhpUnit10Scenario(
            'Uncovered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_10-1/src/Uncovered/functions.php',
        );
    }

    private static function phpUnit11InfoProvider(): iterable
    {
        $phpunit11IndexPath = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-11/coverage-xml/index.xml');

        $createPhpUnit11Scenario = static fn (
            string $relativeCoverageFilePath,
            string $expected,
        ) => [
            $phpunit11IndexPath,
            dirname($phpunit11IndexPath),
            $relativeCoverageFilePath,
            '/path/to/infection/tests/e2e/PHPUnit_11/src',
            $expected,
        ];

        yield 'covered class' => $createPhpUnit11Scenario(
            'Covered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_11/src/Covered/Calculator.php',
        );

        yield 'covered trait' => $createPhpUnit11Scenario(
            'Covered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_11/src/Covered/LoggerTrait.php',
        );

        yield 'covered class with trait' => $createPhpUnit11Scenario(
            'Covered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_11/src/Covered/UserService.php',
        );

        yield 'covered function' => $createPhpUnit11Scenario(
            'Covered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_11/src/Covered/functions.php',
        );

        yield 'uncovered class' => $createPhpUnit11Scenario(
            'Uncovered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_11/src/Uncovered/Calculator.php',
        );

        yield 'uncovered trait' => $createPhpUnit11Scenario(
            'Uncovered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_11/src/Uncovered/LoggerTrait.php',
        );

        yield 'uncovered class with trait' => $createPhpUnit11Scenario(
            'Uncovered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_11/src/Uncovered/UserService.php',
        );

        yield 'uncovered function' => $createPhpUnit11Scenario(
            'Uncovered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_11/src/Uncovered/functions.php',
        );
    }

    private static function phpUnit120InfoProvider(): iterable
    {
        $phpunit120IndexPath = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-0/coverage-xml/index.xml');

        $createPhpUnit120Scenario = static fn (
            string $relativeCoverageFilePath,
            string $expected,
        ) => [
            $phpunit120IndexPath,
            dirname($phpunit120IndexPath),
            $relativeCoverageFilePath,
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src',
            $expected,
        ];

        yield 'covered class' => $createPhpUnit120Scenario(
            'Covered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/Calculator.php',
        );

        yield 'covered trait' => $createPhpUnit120Scenario(
            'Covered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/LoggerTrait.php',
        );

        yield 'covered class with trait' => $createPhpUnit120Scenario(
            'Covered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/UserService.php',
        );

        yield 'covered function' => $createPhpUnit120Scenario(
            'Covered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Covered/functions.php',
        );

        yield 'uncovered class' => $createPhpUnit120Scenario(
            'Uncovered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/Calculator.php',
        );

        yield 'uncovered trait' => $createPhpUnit120Scenario(
            'Uncovered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/LoggerTrait.php',
        );

        yield 'uncovered class with trait' => $createPhpUnit120Scenario(
            'Uncovered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/UserService.php',
        );

        yield 'uncovered function' => $createPhpUnit120Scenario(
            'Uncovered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-0/src/Uncovered/functions.php',
        );
    }

    private static function phpUnit125InfoProvider(): iterable
    {
        $phpunit125IndexPath = Path::canonicalize(self::FIXTURES_DIR . '/phpunit-12-5/index.xml');

        $createPhpUnit125Scenario = static fn (
            string $relativeCoverageFilePath,
            string $expected,
        ) => [
            $phpunit125IndexPath,
            dirname($phpunit125IndexPath),
            $relativeCoverageFilePath,
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src',
            $expected,
        ];

        yield 'covered class' => $createPhpUnit125Scenario(
            'Covered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Covered/Calculator.php',
        );

        yield 'covered trait' => $createPhpUnit125Scenario(
            'Covered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Covered/LoggerTrait.php',
        );

        yield 'covered class with trait' => $createPhpUnit125Scenario(
            'Covered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Covered/UserService.php',
        );

        yield 'covered function' => $createPhpUnit125Scenario(
            'Covered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Covered/functions.php',
        );

        yield 'uncovered class' => $createPhpUnit125Scenario(
            'Uncovered/Calculator.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Uncovered/Calculator.php',
        );

        yield 'uncovered trait' => $createPhpUnit125Scenario(
            'Uncovered/LoggerTrait.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Uncovered/LoggerTrait.php',
        );

        yield 'uncovered class with trait' => $createPhpUnit125Scenario(
            'Uncovered/UserService.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Uncovered/UserService.php',
        );

        yield 'uncovered function' => $createPhpUnit125Scenario(
            'Uncovered/functions.php.xml',
            '/path/to/infection/tests/e2e/PHPUnit_12-5/src/Uncovered/functions.php',
        );
    }

    private static function codeceptionInfoProvider(): iterable
    {
        $codeceptionIndexPath = Path::canonicalize(self::FIXTURES_DIR . '/codeception/coverage-xml/index.xml');

        $createCodeceptionScenario = static fn (
            string $relativeCoverageFilePath,
            string $expected,
        ) => [
            $codeceptionIndexPath,
            dirname($codeceptionIndexPath),
            $relativeCoverageFilePath,
            '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src',
            $expected,
        ];

        yield 'covered class' => $createCodeceptionScenario(
            'Covered/Calculator.php.xml',
            '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Covered/Calculator.php',
        );

        yield 'covered trait' => $createCodeceptionScenario(
            'Covered/LoggerTrait.php.xml',
            '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Covered/LoggerTrait.php',
        );

        yield 'covered class with trait' => $createCodeceptionScenario(
            'Covered/UserService.php.xml',
            '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Covered/UserService.php',
        );

        yield 'covered function' => $createCodeceptionScenario(
            'Covered/functions.php.xml',
            '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Covered/functions.php',
        );

        yield 'database class' => $createCodeceptionScenario(
            'Database.php.xml',
            '/path/to/codeception-adapter/tests/e2e/Codeception_With_Suite_Overridings/src/Database.php',
        );
    }

    private static function phpSpecInfoProvider(): iterable
    {
        $phpSpecIndexPath = Path::canonicalize(self::FIXTURES_DIR . '/phpspec/index.xml');

        $createPhpSpecScenario = static fn (
            string $relativeCoverageFilePath,
            string $expected,
        ) => [
            $phpSpecIndexPath,
            dirname($phpSpecIndexPath),
            $relativeCoverageFilePath,
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src',
            $expected,
        ];

        yield 'covered class' => $createPhpSpecScenario(
            'Covered/Calculator.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/Calculator.php',
        );

        yield 'covered base class' => $createPhpSpecScenario(
            'Covered/BaseCalculator.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/BaseCalculator.php',
        );

        yield 'covered trait' => $createPhpSpecScenario(
            'Covered/LoggerTrait.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/LoggerTrait.php',
        );

        yield 'covered class with trait' => $createPhpSpecScenario(
            'Covered/UserService.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/UserService.php',
        );

        yield 'covered function' => $createPhpSpecScenario(
            'Covered/functions.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Covered/functions.php',
        );

        yield 'uncovered class' => $createPhpSpecScenario(
            'Uncovered/Calculator.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Uncovered/Calculator.php',
        );

        yield 'uncovered trait' => $createPhpSpecScenario(
            'Uncovered/LoggerTrait.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Uncovered/LoggerTrait.php',
        );

        yield 'uncovered class with trait' => $createPhpSpecScenario(
            'Uncovered/UserService.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Uncovered/UserService.php',
        );

        yield 'uncovered function' => $createPhpSpecScenario(
            'Uncovered/functions.php.xml',
            '/path/to/phpspec-adapter/tests/e2e/PhpSpec/src/Uncovered/functions.php',
        );
    }
}
