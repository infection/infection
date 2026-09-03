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

use DOMNodeList;
use function escapeshellarg;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\FileSystem\FileSystem;
use Infection\FileSystem\InMemoryFileSystem;
use Infection\StreamWrapper\IncludeInterceptor;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\Tracing\TestRunOrderResolver;
use Infection\TestFramework\XML\SafeDOMXPath;
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

    private MutationConfigBuilder $builder;

    protected function setUp(): void
    {
        $this->projectPath = Path::canonicalize(self::FIXTURES . '/project-path');
        $this->filesystem = new InMemoryFileSystem();

        $this->builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit.xml');
    }

    /**
     * @param TestLocation[] $tests
     */
    #[DataProvider('configurationProvider')]
    public function test_it_builds_the_xml_configuration(
        string $fixture,
        array $tests,
        string $version,
        string $expectedXml,
    ): void {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/' . $fixture);

        $configurationPath = $builder->build(
            $tests,
            self::MUTATED_FILE_PATH,
            self::HASH,
            self::ORIGINAL_FILE_PATH,
            $version,
        );

        $this->assertSame(
            self::TMP_DIR . '/phpunitConfiguration.a1b2c3.infection.xml',
            $configurationPath,
        );

        $this->assertSame($expectedXml, $this->filesystem->readFile($configurationPath));
    }

    public function test_it_can_build_the_config_for_multiple_mutations(): void
    {
        $tmp = self::TMP_DIR;
        $projectPath = $this->projectPath;
        $interceptorPath = IncludeInterceptor::LOCATION;

        $this->assertSame(
            <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <!--
                  ~ Copyright © 2017 Maks Rafalko
                  ~
                  ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
                  -->
                <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$tmp/interceptor.autoload.hash1.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false">
                  <testsuites>
                    <testsuite name="Infection testsuite with filtered tests">
                      <file>/path/to/FooTest.php</file>
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

                XML,
            $this->filesystem->readFile(
                $this->builder->build(
                    [
                        new TestLocation(
                            'FooTest::test_foo',
                            '/path/to/FooTest.php',
                            1.,
                        ),
                    ],
                    self::MUTATED_FILE_PATH,
                    'hash1',
                    self::ORIGINAL_FILE_PATH,
                    '7.1',
                ),
            ),
        );

        $phpCode = $this->filesystem->readFile(
            self::TMP_DIR . '/interceptor.autoload.hash1.infection.php',
        );

        $this->assertSame(
            <<<PHP
                <?php

                if (function_exists('proc_nice')) {
                    proc_nice(1);
                }

                require_once '$interceptorPath';

                use Infection\StreamWrapper\IncludeInterceptor;

                IncludeInterceptor::intercept('/original/file/path', '/mutated/file/path');
                IncludeInterceptor::enable();
                require_once '$projectPath/app/autoload2.php';

                PHP,
            $phpCode,
        );

        $this->assertPHPSyntaxIsValid($phpCode);

        $this->assertSame(
            <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <!--
                  ~ Copyright © 2017 Maks Rafalko
                  ~
                  ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
                  -->
                <phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$tmp/interceptor.autoload.hash2.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false">
                  <testsuites>
                    <testsuite name="Infection testsuite with filtered tests">
                      <file>/path/to/BarTest.php</file>
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

                XML,
            $this->filesystem->readFile(
                $this->builder->build(
                    [
                        new TestLocation(
                            'BarTest::test_bar_1',
                            '/path/to/BarTest.php',
                            1.,
                        ),
                    ],
                    self::MUTATED_FILE_PATH,
                    'hash2',
                    self::ORIGINAL_FILE_PATH,
                    '7.1',
                ),
            ),
        );

        $phpCode = $this->filesystem->readFile(
            self::TMP_DIR . '/interceptor.autoload.hash2.infection.php',
        );

        $this->assertSame(
            <<<PHP
                <?php

                if (function_exists('proc_nice')) {
                    proc_nice(1);
                }

                require_once '$interceptorPath';

                use Infection\StreamWrapper\IncludeInterceptor;

                IncludeInterceptor::intercept('/original/file/path', '/mutated/file/path');
                IncludeInterceptor::enable();
                require_once '$projectPath/app/autoload2.php';

                PHP,
            $phpCode,
        );

        $this->assertPHPSyntaxIsValid($phpCode);
    }

    public function test_it_parses_the_original_configuration_only_once(): void
    {
        $getXPath = new ReflectionMethod($this->builder, 'getXPath');

        $this->assertSame(
            $getXPath->invoke($this->builder),
            $getXPath->invoke($this->builder),
        );
    }

    public function test_it_sets_custom_autoloader(): void
    {
        $xml = $this->filesystem->readFile(
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '7.1',
            ),
        );

        $resultAutoLoaderFilePath = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            self::TMP_DIR,
            self::HASH,
        );

        $this->assertSame($expectedCustomAutoloadFilePath, $resultAutoLoaderFilePath);
        $this->assertStringContainsString(
            'app/autoload2.php',
            $this->filesystem->readFile($expectedCustomAutoloadFilePath),
        );
    }

    public function test_it_sets_custom_autoloader_when_attribute_is_absent(): void
    {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit_without_bootstrap.xml');

        $xml = $this->filesystem->readFile(
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '7.1',
            ),
        );

        $resultAutoLoaderFilePath = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            self::TMP_DIR,
            self::HASH,
        );

        $this->assertSame($expectedCustomAutoloadFilePath, $resultAutoLoaderFilePath);
        $this->assertStringContainsString(
            'vendor/autoload.php',
            $this->filesystem->readFile($expectedCustomAutoloadFilePath),
        );
    }

    public function test_interceptor_is_included(): void
    {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit_without_bootstrap.xml');

        $builder->build(
            [],
            self::MUTATED_FILE_PATH,
            self::HASH,
            self::ORIGINAL_FILE_PATH,
            '7.1',
        );

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            self::TMP_DIR,
            self::HASH,
        );

        $this->assertStringContainsString(
            'IncludeInterceptor.php',
            $this->filesystem->readFile($expectedCustomAutoloadFilePath),
        );
    }

    public static function configurationProvider(): iterable
    {
        $tmp = self::TMP_DIR;
        $projectPath = Path::canonicalize(self::FIXTURES . '/project-path');
        $configuration = static fn (string $attributes): string => <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit $attributes>
              <testsuites>
                <testsuite name="Infection testsuite with filtered tests"/>
              </testsuites>
            </phpunit>

            XML;

        yield 'standard configuration' => [
            'phpunit.xml',
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
            'phpunit_without_bootstrap.xml',
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
            'phpunit_root_test_suite.xml',
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
            'phpunit_without_coverage_whitelist.xml',
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
            'phpunit_without_coverage_whitelist.xml',
            [],
            '5.1.99',
            $configuration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 5.2 adds fail-on attributes' => [
            'phpunit_without_coverage_whitelist.xml',
            [],
            '5.2',
            $configuration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'an existing failOnRisky value is preserved' => [
            'phpunit_with_fail_on_risky_set.xml',
            [],
            '5.2',
            $configuration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="reverse" failOnRisky="false" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'an existing failOnWarning value is preserved' => [
            'phpunit_with_fail_on_warning_set.xml',
            [],
            '5.2',
            $configuration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="reverse" failOnWarning="false" failOnRisky="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 7.2 sets the default execution order when absent' => [
            'phpunit_without_coverage_whitelist.xml',
            [],
            '7.2',
            $configuration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="default" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 7.2 replaces an existing execution order' => [
            'phpunit_with_order_set.xml',
            [],
            '7.2',
            $configuration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="default" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];

        yield 'PHPUnit 7.3 enables result caching and defect ordering' => [
            'phpunit_with_order_set.xml',
            [],
            '7.3',
            $configuration('backupGlobals="false" backupStaticAttributes="false" bootstrap="/tmp/infection/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" executionOrder="defects" cacheResult="true" cacheResultFile=".phpunit.result.cache.a1b2c3" failOnRisky="true" failOnWarning="true" stopOnFailure="true" stderr="false"'),
        ];
    }

    private function queryXpath(string $xml, string $query): DOMNodeList
    {
        return SafeDOMXPath::fromString($xml)->queryList($query);
    }

    private function createConfigBuilder(
        ?string $originalPhpUnitXmlConfigPath = null,
    ): MutationConfigBuilder {
        $phpunitXmlPath = $originalPhpUnitXmlConfigPath ?: self::FIXTURES . '/phpunit.xml';

        $replacer = new PathReplacer(new FileSystem(), $this->projectPath);

        return new MutationConfigBuilder(
            self::TMP_DIR,
            file_get_contents($phpunitXmlPath),
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
