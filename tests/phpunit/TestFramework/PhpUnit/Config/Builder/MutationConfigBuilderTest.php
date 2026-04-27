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

use function array_map;
use DOMNameSpaceNode;
use DOMNode;
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
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\exec;
use function Safe\file_get_contents;
use function Safe\simplexml_load_string;
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

    public function test_it_builds_and_dump_the_xml_configuration(): void
    {
        $configurationPath = $this->builder->build(
            [],
            self::MUTATED_FILE_PATH,
            self::HASH,
            self::ORIGINAL_FILE_PATH,
            '7.1',
        );

        $this->assertSame(
            self::TMP_DIR . '/phpunitConfiguration.a1b2c3.infection.xml',
            $configurationPath,
        );

        $xml = $this->filesystem->readFile($configurationPath);

        $this->assertNotFalse(
            @simplexml_load_string($xml),
            'Expected dumped configuration content to be a valid XML file.',
        );

        $this->assertTrue(
            $this->filesystem->isReadableFile(self::TMP_DIR . '/interceptor.autoload.a1b2c3.infection.php'),
        );
    }

    public function test_it_preserves_white_spaces_and_formatting(): void
    {
        $configurationPath = $this->builder->build(
            [],
            self::MUTATED_FILE_PATH,
            self::HASH,
            self::ORIGINAL_FILE_PATH,
            '7.1',
        );

        $tmp = self::TMP_DIR;
        $projectPath = $this->projectPath;

        $this->assertSame(
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
            $this->filesystem->readFile($configurationPath),
        );
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

    public function test_it_builds_path_to_mutation_config_file(): void
    {
        $this->assertSame(
            self::TMP_DIR . '/phpunitConfiguration.a1b2c3.infection.xml',
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '7.1',
            ),
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

    public function test_it_sets_stops_on_failure(): void
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

        $stopOnFailure = $this->queryXpath($xml, '/phpunit/@stopOnFailure')[0]->nodeValue;

        $this->assertSame('true', $stopOnFailure);
    }

    public function test_it_deactivates_the_colors(): void
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

        $colors = $this->queryXpath($xml, '/phpunit/@colors')[0]->nodeValue;

        $this->assertSame('false', $colors);
    }

    public function test_it_handles_root_test_suite(): void
    {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit_root_test_suite.xml');

        $configurationPath = $builder->build(
            [],
            self::MUTATED_FILE_PATH,
            self::HASH,
            self::ORIGINAL_FILE_PATH,
            '7.1',
        );

        $testSuite = $this->queryXpath(
            $this->filesystem->readFile($configurationPath),
            '/phpunit/testsuite',
        );

        $this->assertInstanceOf(DOMNodeList::class, $testSuite);
        $this->assertSame(1, $testSuite->length);
    }

    public function test_it_removes_original_loggers(): void
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

        $logEntries = $this->queryXpath($xml, '/phpunit/logging/log[@type="coverage-html"]');

        $this->assertInstanceOf(DOMNodeList::class, $logEntries);
        $this->assertSame(0, $logEntries->length);
    }

    public function test_it_removes_printer_class(): void
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

        $printerClass = $this->queryXpath($xml, '/phpunit/@printerClass');

        $this->assertInstanceOf(DOMNodeList::class, $printerClass);
        $this->assertSame(0, $printerClass->length);
    }

    public function test_it_does_not_set_default_execution_order_for_phpunit_7_1(): void
    {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit_without_coverage_whitelist.xml');

        $xml = $this->filesystem->readFile(
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '7.1',
            ),
        );

        $executionOrder = $this->queryXpath($xml, '/phpunit/@executionOrder');

        $this->assertSame(0, $executionOrder->length);
    }

    public function test_it_sets_default_execution_order_when_attribute_is_absent_for_phpunit_7_2(): void
    {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit_without_coverage_whitelist.xml');

        $xml = $this->filesystem->readFile(
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '7.2',
            ),
        );

        $executionOrder = $this->queryXpath($xml, '/phpunit/@executionOrder')[0]->nodeValue;

        $this->assertSame('default', $executionOrder);
    }

    public function test_it_sets_default_execution_order_when_attribute_is_present_for_phpunit_7_2(): void
    {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit_with_order_set.xml');

        $xml = $this->filesystem->readFile(
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '7.2',
            ),
        );

        $executionOrder = $this->queryXpath($xml, '/phpunit/@executionOrder')[0]->nodeValue;

        $this->assertSame('default', $executionOrder);
    }

    public function test_it_sets_defects_execution_order_and_cache_result_when_attribute_is_present_for_phpunit_7_3(): void
    {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit_with_order_set.xml');

        $xml = $this->filesystem->readFile(
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '7.3',
            ),
        );

        $executionOrder = $this->queryXpath($xml, '/phpunit/@executionOrder')[0]->nodeValue;
        $this->assertSame('defects', $executionOrder);

        $executionOrder = $this->queryXpath($xml, '/phpunit/@cacheResult')[0]->nodeValue;
        $this->assertSame('true', $executionOrder);

        $executionOrder = $this->queryXpath($xml, '/phpunit/@cacheResultFile')[0]->nodeValue;
        $this->assertSame(sprintf('.phpunit.result.cache.%s', self::HASH), $executionOrder);
    }

    /**
     * @param TestLocation[] $tests
     * @param string[] $expectedFiles
     */
    #[DataProvider('locationsProvider')]
    public function test_it_sets_sorted_list_of_test_files(
        array $tests,
        array $expectedFiles,
    ): void {
        $xml = $this->filesystem->readFile(
            $this->builder->build(
                $tests,
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '7.1',
            ),
        );

        $files = array_map(
            static fn (DOMNode|DOMNameSpaceNode $file): string => $file->nodeValue,
            iterator_to_array(
                $this->queryXpath($xml, '/phpunit/testsuites/testsuite/file'),
                false,
            ),
        );

        $this->assertSame($expectedFiles, $files);
    }

    public function test_it_removes_default_test_suite(): void
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

        $defaultTestSuite = $this->queryXpath($xml, '/phpunit/@defaultTestSuite');

        $this->assertInstanceOf(DOMNodeList::class, $defaultTestSuite);
        $this->assertCount(0, $defaultTestSuite);
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

    #[DataProvider('failOnProvider')]
    public function test_it_adds_fail_on_risky_and_warning_for_proper_phpunit_versions(
        string $version,
        string $attributeName,
        int $expectedNodeCount,
    ): void {
        $xml = $this->filesystem->readFile(
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                $version,
            ),
        );

        $nodes = $this->queryXpath($xml, sprintf('/phpunit/@%s', $attributeName));

        $this->assertInstanceOf(DOMNodeList::class, $nodes);

        $this->assertSame($expectedNodeCount, $nodes->length);
    }

    public function test_it_does_not_update_fail_on_risky_attributes_if_it_is_already_set(): void
    {
        $phpunitXmlPath = self::FIXTURES . '/phpunit_with_fail_on_risky_set.xml';

        $builder = $this->createConfigBuilder($phpunitXmlPath);

        $xml = $this->filesystem->readFile(
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '5.2',
            ),
        );

        $failOnRisky = $this->queryXpath($xml, sprintf('/phpunit/@%s', 'failOnRisky'));

        $this->assertInstanceOf(DOMNodeList::class, $failOnRisky);
        $this->assertSame('false', $failOnRisky[0]->value);
    }

    public function test_it_does_not_update_fail_on_warning_attributes_if_it_is_already_set(): void
    {
        $phpunitXmlPath = self::FIXTURES . '/phpunit_with_fail_on_warning_set.xml';

        $builder = $this->createConfigBuilder($phpunitXmlPath);

        $xml = $this->filesystem->readFile(
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH,
                '5.2',
            ),
        );

        $failOnRisky = $this->queryXpath($xml, sprintf('/phpunit/@%s', 'failOnWarning'));

        $this->assertInstanceOf(DOMNodeList::class, $failOnRisky);
        $this->assertSame('false', $failOnRisky[0]->value);
    }

    public static function failOnProvider(): iterable
    {
        yield 'PHPUnit 5.1.99 runs without failOnRisky' => [
            '5.1.99',
            'failOnRisky',
            0,
        ];

        yield 'PHPUnit 5.2 runs with failOnRisky' => [
            '5.2',
            'failOnRisky',
            1,
        ];

        yield 'PHPUnit 5.3.1 runs with failOnRisky' => [
            '5.3.1',
            'failOnRisky',
            1,
        ];

        yield 'PHPUnit 5.1.99 runs without resolveDependencies' => [
            '5.1.99',
            'failOnWarning',
            0,
        ];

        yield 'PHPUnit 5.2 runs with resolveDependencies' => [
            '5.2',
            'failOnWarning',
            1,
        ];

        yield 'PHPUnit 5.3.1 runs resolveDependencies' => [
            '5.3.1',
            'failOnWarning',
            1,
        ];
    }

    public static function locationsProvider(): iterable
    {
        yield [
            [
                new TestLocation(
                    'SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage with data set #5',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                    0.861780,
                ),
                new TestLocation(
                    'SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage with data set #6',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                    0.861780,
                ),
                new TestLocation(
                    'SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest::it_correctly_returns_id',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                    0.035935,
                ),
                new TestLocation(
                    'SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest::it_correctly_returns_recorded_at_date',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                    0.035935,
                ),
            ],
            [
                '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
            ],
        ];

        yield [
            [
                new TestLocation(
                    'Path\\To\\A::test_a',
                    '/path/to/A.php',
                    0.586178,
                ),
                new TestLocation(
                    'Path\\To\\B::test_b',
                    '/path/to/B.php',
                    0.186178,
                ),
                new TestLocation(
                    'Path\\To\\C::test_c',
                    '/path/to/C.php',
                    0.016178,
                ),
            ],
            [
                '/path/to/C.php',
                '/path/to/B.php',
                '/path/to/A.php',
            ],
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
