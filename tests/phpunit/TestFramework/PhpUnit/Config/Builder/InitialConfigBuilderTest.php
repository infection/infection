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

use Infection\FileSystem\FileSystem;
use Infection\FileSystem\InMemoryFileSystem;
use Infection\Framework\OperatingSystem;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\InvalidPhpUnitConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\simplexml_load_string;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(InitialConfigBuilder::class)]
final class InitialConfigBuilderTest extends TestCase
{
    private const string FIXTURES = __DIR__ . '/Fixtures';

    private const string TMP_DIR = '/tmp/infection';

    private string $projectPath;

    private FileSystem $filesystem;

    private InitialConfigBuilder $builder;

    protected function setUp(): void
    {
        $this->projectPath = Path::canonicalize(self::FIXTURES . '/project-path');
        $this->filesystem = new InMemoryFileSystem();

        $this->builder = $this->createConfigBuilder();
    }

    public function test_it_builds_and_dump_the_xml_configuration(): void
    {
        $configurationPath = $this->builder->build('6.5');

        $this->assertSame(
            self::TMP_DIR . '/phpunitConfiguration.initial.infection.xml',
            $configurationPath,
        );

        $xml = $this->filesystem->readFile($configurationPath);

        $this->assertNotFalse(
            @simplexml_load_string($xml),
            'Expected dumped configuration content to be a valid XML file.',
        );
    }

    public function test_the_original_xml_config_must_be_a_valid_xml_file(): void
    {
        try {
            $this->createConfigBuilder(
                self::FIXTURES . '/invalid/empty-phpunit.xml',
            );

            $this->fail('Expected an exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'The original XML config content cannot be an empty string',
                $exception->getMessage(),
            );
        }
    }

    public function test_the_original_xml_config_must_be_a_valid_phpunit_config_file(): void
    {
        $builder = $this->createConfigBuilder(
            self::FIXTURES . '/invalid/invalid-phpunit.xml',
        );

        try {
            $builder->build('x');

            $this->fail('Expected an exception to be thrown.');
        } catch (InvalidPhpUnitConfiguration $exception) {
            $this->assertSame(
                sprintf(
                    'The file "%s/phpunitConfiguration.initial.infection.xml" is not a valid PHPUnit configuration file',
                    self::TMP_DIR,
                ),
                $exception->getMessage(),
            );
        }
    }

    /**
     * @param list<string> $filteredSourceFilesToMutate
     */
    #[DataProvider('configurationProvider')]
    public function test_it_builds_the_expected_configuration(
        string $fixture,
        array $filteredSourceFilesToMutate,
        string $version,
        string $expected,
    ): void {
        $builder = $this->createConfigBuilder(
            self::FIXTURES . '/' . $fixture,
            $filteredSourceFilesToMutate,
        );

        $path = $builder->build($version);
        $actual = $this->filesystem->readFile($path);

        $this->assertSame($expected, $actual);
    }

    public static function configurationProvider(): iterable
    {
        $projectPath = Path::canonicalize(self::FIXTURES . '/project-path');

        $coverage10 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnDefect="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <coverage>
                <include>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </include>
              </coverage>
            </phpunit>

            XML;

        $executionOrder12 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <!--
              ~ Copyright © 2017 Maks Rafalko
              ~
              ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
              -->
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" defaultTestSuite="unit" executionOrder="defects,random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnDefect="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
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
              <source>
                <include>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </include>
              </source>
            </phpunit>

            XML;

        $executionOrder132 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit bootstrap="$projectPath/app/autoload2.php" colors="false" cacheDirectory=".phpunit.cache" executionOrder="depends,defects,duration-ascending" failOnRisky="true" failOnWarning="true" stopOnDefect="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <source>
                <include>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </include>
              </source>
            </phpunit>

            XML;

        $executionOrder133 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit bootstrap="$projectPath/app/autoload2.php" colors="false" cacheDirectory=".phpunit.cache" executionOrder="depends" failOnRisky="true" failOnWarning="true" stopOnDefect="true" recordTestRunHistory="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <source>
                <include>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </include>
              </source>
            </phpunit>

            XML;

        $executionOrder72 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <!--
              ~ Copyright © 2017 Maks Rafalko
              ~
              ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
              -->
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" defaultTestSuite="unit" executionOrder="random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
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

            XML;

        $existingExecutionOrder = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="reverse" failOnRisky="true" failOnWarning="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <filter>
                <whitelist>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </whitelist>
              </filter>
            </phpunit>

            XML;

        $existingFailOnRisky = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="reverse" failOnRisky="false" failOnWarning="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <filter>
                <whitelist>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </whitelist>
              </filter>
            </phpunit>

            XML;

        $existingFailOnWarning = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="reverse" failOnWarning="false" failOnRisky="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <filter>
                <whitelist>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </whitelist>
              </filter>
            </phpunit>

            XML;

        $filteredSource93 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <coverage>
                <include>
                  <file>$projectPath/src/File1.php</file>
                </include>
              </coverage>
            </phpunit>

            XML;

        $missingWhitelist65 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <filter>
                <whitelist>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </whitelist>
              </filter>
            </phpunit>

            XML;

        $missingWhitelist93 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <filter>
                <whitelist>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </whitelist>
              </filter>
            </phpunit>

            XML;

        $phpunit133 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <!--
              ~ Copyright © 2017 Maks Rafalko
              ~
              ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
              -->
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" defaultTestSuite="unit" executionOrder="random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnDefect="true" recordTestRunHistory="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
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
              <source>
                <include>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </include>
              </source>
            </phpunit>

            XML;

        $phpunit51 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <!--
              ~ Copyright © 2017 Maks Rafalko
              ~
              ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
              -->
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" defaultTestSuite="unit" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
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

            XML;

        $phpunit94 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" defaultTestSuite="unit" executionOrder="random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <coverage includeUncoveredFiles="true" processUncoveredFiles="true" ignoreDeprecatedCodeUnits="true" disableCodeCoverageIgnore="true">
                <include>
                  <directory suffix=".php">$projectPath/src</directory>
                </include>
                <exclude>
                  <directory suffix=".php">$projectPath/src/generated</directory>
                  <file>$projectPath/src/autoload.php</file>
                </exclude>
              </coverage>
            </phpunit>

            XML;

        $preservedCoverage12 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnDefect="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <coverage>
                <include>
                  <directory>$projectPath/src/</directory>
                  <directory>$projectPath/example/</directory>
                </include>
              </coverage>
              <source>
                <include>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </include>
              </source>
            </phpunit>

            XML;

        $source101 = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="random" resolveDependencies="true" failOnRisky="true" failOnWarning="true" stopOnDefect="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
              </testsuites>
              <source>
                <include>
                  <directory>$projectPath/src</directory>
                  <directory>$projectPath/app</directory>
                </include>
              </source>
            </phpunit>

            XML;

        $standard = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <!--
              ~ Copyright © 2017 Maks Rafalko
              ~
              ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
              -->
            <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$projectPath/app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" defaultTestSuite="unit" failOnRisky="true" failOnWarning="true" stopOnFailure="true" cacheResult="false" stderr="false">
              <testsuites>
                <testsuite name="Application Test Suite">
                  <directory>$projectPath/*Bundle</directory>
                </testsuite>
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

            XML;

        yield 'configuration is created' => self::createScenario($standard);

        if (!OperatingSystem::isWindows()) {
            yield 'white spaces and formatting are preserved' => [
                'format-whitespace/original-phpunit.xml',
                [],
                '6.5',
                file_get_contents(self::FIXTURES . '/format-whitespace/expected-phpunit.xml'),
            ];
        }

        yield 'relative paths are replaced with absolute paths' => self::createScenario($standard);

        yield 'stop on failure is enabled' => self::createScenario($standard);

        yield 'colors are disabled' => self::createScenario($standard);

        yield 'result caching is disabled' => self::createScenario($standard);

        yield 'PHPUnit 13.3 test run history is disabled' => self::createScenario(
            $phpunit133,
            version: '13.3',
        );

        yield 'stderr redirection is disabled' => self::createScenario($standard);

        yield 'bootstrap path is replaced' => self::createScenario($standard);

        yield 'original loggers are removed' => self::createScenario($standard);

        yield 'coverage loggers are not added to legacy configuration' => self::createScenario($standard);

        yield 'coverage loggers are not added to latest configuration' => self::createScenario(
            $phpunit94,
            version: '9.4',
            fixture: 'phpunit_93.xml',
        );

        yield 'missing legacy coverage whitelist is created' => self::createScenario(
            $missingWhitelist65,
            fixture: 'phpunit_without_coverage_whitelist.xml',
        );

        yield 'missing legacy coverage whitelist is created for uncertain versions' => self::createScenario(
            $missingWhitelist93,
            version: '9.3',
            fixture: 'phpunit_without_coverage_whitelist.xml',
        );

        yield 'coverage include is replaced when filtered source files are provided' => self::createScenario(
            $filteredSource93,
            version: '9.3',
            fixture: 'phpunit_with_coverage_include_directories.xml',
            filteredSourceFilesToMutate: ['src/File1.php'],
        );

        yield 'PHPUnit 12 coverage include is preserved with filtered source files' => self::createScenario(
            $preservedCoverage12,
            version: '12.0',
            fixture: 'phpunit_with_coverage_include_directories.xml',
            filteredSourceFilesToMutate: ['src/File1.php'],
        );

        yield 'PHPUnit 12 ignores filtered source files when creating source include' => self::createScenario(
            $source101,
            version: '12.0',
            fixture: 'phpunit_without_coverage_whitelist.xml',
            filteredSourceFilesToMutate: ['src/File1.php'],
        );

        yield 'PHPUnit 10.0 coverage include is created when absent' => self::createScenario(
            $coverage10,
            version: '10.0',
            fixture: 'phpunit_without_coverage_whitelist.xml',
        );

        yield 'PHPUnit 10.0 legacy coverage whitelist is not created' => self::createScenario(
            $coverage10,
            version: '10.0',
            fixture: 'phpunit_without_coverage_whitelist.xml',
        );

        yield 'PHPUnit 10.1 source include is created when absent' => self::createScenario(
            $source101,
            version: '10.1',
            fixture: 'phpunit_without_coverage_whitelist.xml',
        );

        yield 'existing legacy coverage whitelist is preserved' => self::createScenario($standard);

        yield 'existing coverage include is preserved' => self::createScenario(
            $phpunit94,
            version: '9.4',
            fixture: 'phpunit_93.xml',
        );

        yield 'printer class is removed' => self::createScenario($standard);

        yield 'PHPUnit 7.1.99 runs without random test order' => self::createScenario(
            $standard,
            version: '7.1.99',
        );

        yield 'PHPUnit 7.2 runs with random test order' => self::createScenario(
            $executionOrder72,
            version: '7.2',
        );

        yield 'PHPUnit 7.3.1 runs with random test order' => self::createScenario(
            $executionOrder72,
            version: '7.3.1',
        );

        yield 'PHPUnit 7.1.99 runs without dependency resolver' => self::createScenario(
            $standard,
            version: '7.1.99',
        );

        yield 'PHPUnit 7.2 runs with dependency resolver' => self::createScenario(
            $executionOrder72,
            version: '7.2',
        );

        yield 'PHPUnit 7.3.1 runs dependency resolver' => self::createScenario(
            $executionOrder72,
            version: '7.3.1',
        );

        yield 'PHPUnit 12.2.7 orders by defects and randomly' => self::createScenario(
            $executionOrder12,
            version: '12.2.7',
        );

        yield 'PHPUnit 13.3 only orders randomly without test run history' => self::createScenario(
            $phpunit133,
            version: '13.3',
        );

        yield 'existing execution order is preserved' => self::createScenario(
            $existingExecutionOrder,
            version: '7.2',
            fixture: 'phpunit_with_order_set.xml',
        );

        yield 'PHPUnit 13.3 removes orders requiring test run history' => self::createScenario(
            $executionOrder133,
            version: '13.3',
            fixture: 'phpunit_with_order_requiring_test_run_history_set.xml',
        );

        yield 'PHPUnit 13.2 preserves orders requiring test run history' => self::createScenario(
            $executionOrder132,
            version: '13.2',
            fixture: 'phpunit_with_order_requiring_test_run_history_set.xml',
        );

        yield 'PHPUnit 5.1.99 runs without failOnRisky' => self::createScenario(
            $phpunit51,
            version: '5.1.99',
        );

        yield 'PHPUnit 5.2 runs with failOnRisky' => self::createScenario($standard, version: '5.2');

        yield 'PHPUnit 5.3.1 runs with failOnRisky' => self::createScenario($standard, version: '5.3.1');

        yield 'PHPUnit 5.1.99 runs without failOnWarning' => self::createScenario(
            $phpunit51,
            version: '5.1.99',
        );

        yield 'PHPUnit 5.2 runs with failOnWarning' => self::createScenario($standard, version: '5.2');

        yield 'PHPUnit 5.3.1 runs with failOnWarning' => self::createScenario($standard, version: '5.3.1');

        yield 'existing failOnRisky is preserved' => self::createScenario(
            $existingFailOnRisky,
            version: '5.2',
            fixture: 'phpunit_with_fail_on_risky_set.xml',
        );

        yield 'existing failOnWarning is preserved' => self::createScenario(
            $existingFailOnWarning,
            version: '5.2',
            fixture: 'phpunit_with_fail_on_warning_set.xml',
        );
    }

    /**
     * @param list<string> $filteredSourceFilesToMutate
     * @return array{string, list<string>, string, string}
     */
    private static function createScenario(
        string $expected,
        string $version = '6.5',
        string $fixture = 'phpunit.xml',
        array $filteredSourceFilesToMutate = [],
    ): array {
        return [
            $fixture,
            $filteredSourceFilesToMutate,
            $version,
            $expected,
        ];
    }

    /**
     * @param list<string> $filteredSourceFilesToMutate
     */
    private function createConfigBuilder(
        ?string $originalPhpUnitXmlConfigPath = null,
        array $filteredSourceFilesToMutate = [],
    ): InitialConfigBuilder {
        $phpunitXmlPath = $originalPhpUnitXmlConfigPath ?: self::FIXTURES . '/phpunit.xml';

        $srcDirs = ['src', 'app'];

        $replacer = new PathReplacer(
            $this->filesystem,
            $this->projectPath,
        );

        return new InitialConfigBuilder(
            self::TMP_DIR,
            file_get_contents($phpunitXmlPath),
            new XmlConfigurationManipulator($replacer, ''),
            new XmlConfigurationVersionProvider(),
            $this->filesystem,
            $srcDirs,
            $filteredSourceFilesToMutate,
        );
    }
}
