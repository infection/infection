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

namespace Infection\Tests\TestFramework\PhpUnit\Config\Builder;

use function escapeshellarg;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\FileSystem\FileSystem;
use Infection\FileSystem\InMemoryFileSystem;
use Infection\StreamWrapper\IncludeInterceptor;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\Tracing\TestRunOrderResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use function Safe\exec;
use function Safe\file_get_contents;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(MutationConfigBuilder::class)]
final class MutationConfigBuilderTest extends TestCase
{
    public const string HASH = 'a1b2c3';

    private const string FIXTURES = __DIR__ . '/Fixtures';

    private const string TMP_DIR = '/tmp/infection';

    private const string ORIGINAL_FILE_PATH = '/original/file/path';

    private const string MUTATED_FILE_PATH = '/mutated/file/path';

    private string $projectPath;

    private FileSystem $filesystem;

    protected function setUp(): void
    {
        $this->projectPath = Path::canonicalize(self::FIXTURES . '/project-path');
        $this->filesystem = new InMemoryFileSystem();
    }

    /**
     * @param TestLocation[] $tests
     */
    #[DataProvider('configurationProvider')]
    public function test_it_builds_the_xml_configuration(
        string $xml,
        array $tests,
        string $version,
        string $expectedXml,
    ): void {
        $builder = $this->createBuilder($xml);

        $actualConfigurationPath = $builder->build(
            $tests,
            self::MUTATED_FILE_PATH,
            self::HASH,
            self::ORIGINAL_FILE_PATH,
            $version,
        );

        $expectedConfigurationPath = self::TMP_DIR . '/phpunitConfiguration.a1b2c3.infection.xml';
        $this->assertSame($expectedConfigurationPath, $actualConfigurationPath);

        $actualXml = $this->filesystem->readFile($actualConfigurationPath);
        $this->assertSame($expectedXml, $actualXml);
    }

    #[DataProvider('autoloadProvider')]
    public function test_it_builds_the_autoload_file(
        string $xml,
        string $expectedAutoload,
    ): void {
        $builder = $this->createBuilder($xml);

        $builder->build(
            tests: [],  // irrelevant for this test
            mutantFilePath: self::MUTATED_FILE_PATH,
            mutationHash: self::HASH,
            mutationOriginalFilePath: self::ORIGINAL_FILE_PATH,
            version: '7.1',
        );

        $expectedAutoloadPath = self::TMP_DIR . '/interceptor.autoload.a1b2c3.infection.php';
        $actualAutoload = $this->filesystem->readFile($expectedAutoloadPath);

        $this->assertSame($expectedAutoload, $actualAutoload);
        $this->assertPHPSyntaxIsValid($actualAutoload);
    }

    public function test_it_preserves_the_original_bootstrap_when_building_multiple_configurations(): void
    {
        $builder = $this->createBuilder('<phpunit bootstrap="autoload.php"/>');

        $builder->build(
            [new TestLocation('FooTest::test_foo', '/path/to/FooTest.php', 1.)],
            self::MUTATED_FILE_PATH,
            'first',
            self::ORIGINAL_FILE_PATH,
            '7.1',
        );
        $builder->build(
            [new TestLocation('BarTest::test_bar', '/path/to/BarTest.php', 1.)],
            self::MUTATED_FILE_PATH,
            'second',
            self::ORIGINAL_FILE_PATH,
            '7.1',
        );

        $this->assertSame(
            <<<'XML'
                <?xml version="1.0"?>
                <phpunit bootstrap="/tmp/infection/interceptor.autoload.first.infection.php" failOnRisky="true" failOnWarning="true" stopOnFailure="true" colors="false" stderr="false">
                  <testsuite name="Infection testsuite with filtered tests">
                    <file>/path/to/FooTest.php</file>
                  </testsuite>
                </phpunit>

                XML,
            $this->filesystem->readFile(self::TMP_DIR . '/phpunitConfiguration.first.infection.xml'),
        );
        $this->assertSame(
            <<<'XML'
                <?xml version="1.0"?>
                <phpunit bootstrap="/tmp/infection/interceptor.autoload.second.infection.php" failOnRisky="true" failOnWarning="true" stopOnFailure="true" colors="false" stderr="false">
                  <testsuite name="Infection testsuite with filtered tests">
                    <file>/path/to/BarTest.php</file>
                  </testsuite>
                </phpunit>

                XML,
            $this->filesystem->readFile(self::TMP_DIR . '/phpunitConfiguration.second.infection.xml'),
        );

        $interceptorPath = IncludeInterceptor::LOCATION;
        $originalBootstrap = $this->projectPath . '/autoload.php';
        $expectedAutoload = <<<PHP
            <?php

            if (function_exists('proc_nice')) {
                proc_nice(1);
            }

            require_once '$interceptorPath';

            use Infection\StreamWrapper\IncludeInterceptor;

            IncludeInterceptor::intercept('/original/file/path', '/mutated/file/path');
            IncludeInterceptor::enable();
            require_once '$originalBootstrap';

            PHP;

        foreach (['first', 'second'] as $hash) {
            $actualAutoload = $this->filesystem->readFile(
                sprintf('%s/interceptor.autoload.%s.infection.php', self::TMP_DIR, $hash),
            );

            $this->assertSame($expectedAutoload, $actualAutoload);
            $this->assertPHPSyntaxIsValid($actualAutoload);
        }
    }

    public static function configurationProvider(): iterable
    {
        $tmp = self::TMP_DIR;
        $projectPath = Path::canonicalize(self::FIXTURES . '/project-path');
        $loadFixture = static fn (string $fixture): string => file_get_contents(self::FIXTURES . '/' . $fixture);

        $createConfiguration = static fn (string $attributes): string => <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit $attributes>
              <testsuites>
                <testsuite name="Infection testsuite with filtered tests"/>
              </testsuites>
            </phpunit>

            XML;

        yield 'standard configuration' => [
            $loadFixture('phpunit.xml'),
            [],
            '7.1',
            <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <!--
                  ~ Copyright © 2017 Maks Rafalko
                  ~
                  ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
                  -->
                <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$tmp/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false">
                  <testsuites>
                    <testsuite name="Infection testsuite with filtered tests"/>
                  </testsuites>
                  <filter>
                    <whitelist>
                      <directory>$projectPath/src/</directory>
                      <!--<exclude>-->
                      <!--<directory>src/*Bundle/Resources</directory>-->
                      <!--<directory>src/*/*Bundle/Resources</directory>-->
                      <!--<directory>src/*/Bundle/*Bundle/Resources</directory>-->
                      <!--</exclude>-->
                    </whitelist>
                  </filter>
                </phpunit>

                XML,
        ];

        yield 'configuration without a bootstrap' => [
            $loadFixture('phpunit_without_bootstrap.xml'),
            [],
            '7.1',
            <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit failOnRisky="true" failOnWarning="true" stopOnFailure="true" colors="false" stderr="false" bootstrap="$tmp/interceptor.autoload.a1b2c3.infection.php">
                  <testsuites>
                    <testsuite name="Infection testsuite with filtered tests"/>
                  </testsuites>
                </phpunit>

                XML,
        ];

        yield 'root test suite' => [
            $loadFixture('phpunit_root_test_suite.xml'),
            [],
            '7.1',
            <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <!--
                  ~ Copyright © 2017 Maks Rafalko
                  ~
                  ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
                  -->
                <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$tmp/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false">
                  <filter>
                    <whitelist>
                      <directory>$projectPath/src/</directory>
                      <!--<exclude>-->
                      <!--<directory>src/*Bundle/Resources</directory>-->
                      <!--<directory>src/*/*Bundle/Resources</directory>-->
                      <!--<directory>src/*/Bundle/*Bundle/Resources</directory>-->
                      <!--</exclude>-->
                    </whitelist>
                  </filter>
                  <testsuite name="Infection testsuite with filtered tests"/>
                </phpunit>

                XML,
        ];

        yield 'ordered test files' => [
            $loadFixture('phpunit_without_coverage_whitelist.xml'),
            [
                new TestLocation('A::test_a', '/path/to/A.php', 0.5),
                new TestLocation('B::test_b', '/path/to/B.php', 0.1),
            ],
            '7.1',
            <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$tmp/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false">
                  <testsuites>
                    <testsuite name="Infection testsuite with filtered tests">
                      <file>/path/to/B.php</file>
                      <file>/path/to/A.php</file>
                    </testsuite>
                  </testsuites>
                </phpunit>

                XML,
        ];

        yield 'PHPUnit 5.1 does not add fail-on attributes' => [
            $loadFixture('phpunit_without_coverage_whitelist.xml'),
            [],
            '5.1.99',
            $createConfiguration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 5.2 adds fail-on attributes' => [
            $loadFixture('phpunit_without_coverage_whitelist.xml'),
            [],
            '5.2',
            $createConfiguration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 5.3 adds fail-on attributes' => [
            $loadFixture('phpunit_without_coverage_whitelist.xml'),
            [],
            '5.3.1',
            $createConfiguration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'an existing failOnRisky value is preserved' => [
            $loadFixture('phpunit_with_fail_on_risky_set.xml'),
            [],
            '5.2',
            $createConfiguration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="reverse" failOnRisky="false" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'an existing failOnWarning value is preserved' => [
            $loadFixture('phpunit_with_fail_on_warning_set.xml'),
            [],
            '5.2',
            $createConfiguration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="reverse" failOnWarning="false" failOnRisky="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 7.2 sets the default execution order when absent' => [
            $loadFixture('phpunit_without_coverage_whitelist.xml'),
            [],
            '7.2',
            $createConfiguration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="default" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 7.2 replaces an existing execution order' => [
            $loadFixture('phpunit_with_order_set.xml'),
            [],
            '7.2',
            $createConfiguration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="default" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 7.3 enables result caching and defect ordering' => [
            $loadFixture('phpunit_with_order_set.xml'),
            [],
            '7.3',
            $createConfiguration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="defects" cacheResult="true" cacheResultFile=".phpunit.result.cache.a1b2c3" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];
    }

    public static function autoloadProvider(): iterable
    {
        $interceptorPath = IncludeInterceptor::LOCATION;

        $createAutoload = static fn (string $originalAutoload): string => <<<PHP
            <?php

            if (function_exists('proc_nice')) {
                proc_nice(1);
            }

            require_once '$interceptorPath';

            use Infection\StreamWrapper\IncludeInterceptor;

            IncludeInterceptor::intercept('/original/file/path', '/mutated/file/path');
            IncludeInterceptor::enable();
            require_once '$originalAutoload';

            PHP;

        yield 'configuration bootstrap' => [
            '<phpunit bootstrap="app/autoload2.php"/>',
            $createAutoload(Path::canonicalize(self::FIXTURES . '/project-path/app/autoload2.php')),
        ];

        yield 'default Composer autoloader' => [
            '<phpunit/>',
            $createAutoload('project/dir/vendor/autoload.php'),
        ];
    }

    public function test_it_parses_the_original_configuration_only_once(): void
    {
        $build = $this->createBuilder(
            file_get_contents(self::FIXTURES . '/phpunit.xml'),
        );

        $getXPath = new ReflectionMethod($build, 'getXPath');

        $this->assertSame(
            $getXPath->invoke($build),
            $getXPath->invoke($build),
        );
    }

    private function createBuilder(string $xml): MutationConfigBuilder
    {
        $replacer = new PathReplacer(
            $this->filesystem,
            $this->projectPath,
        );

        return new MutationConfigBuilder(
            self::TMP_DIR,
            $xml,
            new XmlConfigurationManipulator($replacer, ''),
            'project/dir',
            new TestRunOrderResolver(),
            $this->filesystem,
        );
    }

    private function assertPHPSyntaxIsValid(string $phpCode): void
    {
        exec(
            sprintf('echo %s | php -l', escapeshellarg($phpCode)),
            $output,
            $returnCode,
        );

        $this->assertSame(
            0,
            $returnCode,
            'Builder produced invalid code',
        );
    }
}
