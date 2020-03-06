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
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Generator;
use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\StreamWrapper\IncludeInterceptor;
use Infection\TestFramework\Coverage\JUnit\JUnitTestCaseSorter;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\Tests\FileSystem\FileSystemTestCase;
use function Infection\Tests\normalizePath as p;
use function iterator_to_array;
use function Safe\file_get_contents;
use function Safe\realpath;
use function Safe\sprintf;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group integration
 */
final class MutationConfigBuilderTest extends FileSystemTestCase
{
    public const HASH = 'a1b2c3';

    private const FIXTURES = __DIR__ . '/../../../../Fixtures/Files/phpunit';
    private const ORIGINAL_FILE_PATH = '/original/file/path';
    private const MUTATED_FILE_PATH = '/mutated/file/path';

    /**
     * @var string
     */
    private $projectPath;

    /**
     * @var MutationConfigBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectPath = p(realpath(self::FIXTURES . '/project-path'));

        $this->builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit.xml');
    }

    public function test_it_builds_and_dump_the_XML_configuration(): void
    {
        $configurationPath = $this->builder->build(
            [],
            self::MUTATED_FILE_PATH,
            self::HASH,
            self::ORIGINAL_FILE_PATH
        );

        $this->assertSame(
            $this->tmp . '/phpunitConfiguration.a1b2c3.infection.xml',
            $configurationPath
        );

        $this->assertFileExists($configurationPath);

        $xml = file_get_contents($configurationPath);

        $this->assertNotFalse(
            @simplexml_load_string($xml),
            'Expected dumped configuration content to be a valid XML file.'
        );

        $this->assertFileExists($this->tmp . '/interceptor.autoload.a1b2c3.infection.php');
    }

    public function test_it_preserves_white_spaces_and_formatting(): void
    {
        $configurationPath = $this->builder->build(
            [],
            self::MUTATED_FILE_PATH,
            self::HASH,
            self::ORIGINAL_FILE_PATH
        );

        $tmp = $this->tmp;
        $projectPath = $this->projectPath;

        $this->assertSame(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!--
  ~ Copyright © 2017 Maks Rafalko
  ~
  ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
  -->
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$tmp/interceptor.autoload.a1b2c3.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" stopOnFailure="true" cacheResult="false" stderr="false">
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

XML
            ,
            file_get_contents($configurationPath)
        );
    }

    public function test_it_can_build_the_config_for_multiple_mutations(): void
    {
        $tmp = $this->tmp;
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
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$tmp/interceptor.autoload.hash1.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" stopOnFailure="true" cacheResult="false" stderr="false">
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

XML
            ,
            file_get_contents(
                $this->builder->build(
                    [
                        CoverageLineData::with(
                            'FooTest::test_foo',
                            '/path/to/FooTest.php',
                            1.
                        ),
                    ],
                    self::MUTATED_FILE_PATH,
                    'hash1',
                    self::ORIGINAL_FILE_PATH
                )
            )
        );
        $this->assertSame(
            <<<PHP
<?php


require_once '$interceptorPath';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('/original/file/path', '/mutated/file/path');
IncludeInterceptor::enable();
require_once '$projectPath/app/autoload2.php';

PHP
            ,
            file_get_contents($this->tmp . '/interceptor.autoload.hash1.infection.php')
        );

        $this->assertSame(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!--
  ~ Copyright © 2017 Maks Rafalko
  ~
  ~ License: https://opensource.org/licenses/BSD-3-Clause New BSD License
  -->
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="$tmp/interceptor.autoload.hash2.infection.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" stopOnFailure="true" cacheResult="false" stderr="false">
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

XML
            ,
            file_get_contents(
                $this->builder->build(
                    [
                        CoverageLineData::with(
                            'BarTest::test_bar_1',
                            '/path/to/BarTest.php',
                            1.
                        ),
                    ],
                    self::MUTATED_FILE_PATH,
                    'hash2',
                    self::ORIGINAL_FILE_PATH
                )
            )
        );
        $this->assertSame(
            <<<PHP
<?php


require_once '$interceptorPath';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('/original/file/path', '/mutated/file/path');
IncludeInterceptor::enable();
require_once '$projectPath/app/autoload2.php';

PHP
            ,
            file_get_contents($this->tmp . '/interceptor.autoload.hash2.infection.php')
        );
    }

    public function test_it_builds_path_to_mutation_config_file(): void
    {
        $this->assertSame(
            $this->tmp . '/phpunitConfiguration.a1b2c3.infection.xml',
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
        );
    }

    public function test_it_sets_custom_autoloader(): void
    {
        $xml = file_get_contents(
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
        );

        $resultAutoLoaderFilePath = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tmp,
            self::HASH
        );

        $this->assertSame($expectedCustomAutoloadFilePath, $resultAutoLoaderFilePath);
        $this->assertStringContainsString(
            'app/autoload2.php',
            file_get_contents($expectedCustomAutoloadFilePath)
        );
    }

    public function test_it_sets_custom_autoloader_when_attribute_is_absent(): void
    {
        $builder = $this->createConfigBuilder(self::FIXTURES . '/phpunit_without_bootstrap.xml');

        $xml = file_get_contents(
            $builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
        );

        $resultAutoLoaderFilePath = $this->queryXpath($xml, '/phpunit/@bootstrap')[0]->nodeValue;

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tmp,
            self::HASH
        );

        $this->assertSame($expectedCustomAutoloadFilePath, $resultAutoLoaderFilePath);
        $this->assertStringContainsString(
            'vendor/autoload.php',
            file_get_contents($expectedCustomAutoloadFilePath)
        );
    }

    public function test_it_sets_stops_on_failure(): void
    {
        $xml = file_get_contents(
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
        );

        $stopOnFailure = $this->queryXpath($xml, '/phpunit/@stopOnFailure')[0]->nodeValue;

        $this->assertSame('true', $stopOnFailure);
    }

    public function test_it_deactivates_the_colors(): void
    {
        $xml = file_get_contents(
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
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
            self::ORIGINAL_FILE_PATH
        );

        $testSuite = $this->queryXpath(
            file_get_contents($configurationPath),
            '/phpunit/testsuite'
        );

        $this->assertInstanceOf(DOMNodeList::class, $testSuite);
        $this->assertSame(1, $testSuite->length);
    }

    public function test_it_removes_original_loggers(): void
    {
        $xml = file_get_contents(
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
        );

        $logEntries = $this->queryXpath($xml, '/phpunit/logging/log[@type="coverage-html"]');

        $this->assertInstanceOf(DOMNodeList::class, $logEntries);
        $this->assertSame(0, $logEntries->length);
    }

    public function test_it_removes_printer_class(): void
    {
        $xml = file_get_contents(
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
        );

        $printerClass = $this->queryXpath($xml, '/phpunit/@printerClass');

        $this->assertInstanceOf(DOMNodeList::class, $printerClass);
        $this->assertSame(0, $printerClass->length);
    }

    /**
     * @dataProvider coverageTestsProvider
     *
     * @param CoverageLineData[] $coverageTests
     * @param string[] $expectedFiles
     */
    public function test_it_sets_sorted_list_of_test_files(
        array $coverageTests,
        array $expectedFiles
    ): void {
        $xml = file_get_contents(
            $this->builder->build(
                $coverageTests,
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
        );

        $files = array_map(
            static function (DOMNode $file): string {
                return $file->nodeValue;
            },
            iterator_to_array(
                $this->queryXpath($xml, '/phpunit/testsuites/testsuite/file'),
                false
            )
        );

        $this->assertSame($expectedFiles, $files);
    }

    public function test_it_removes_default_test_suite(): void
    {
        $xml = file_get_contents(
            $this->builder->build(
                [],
                self::MUTATED_FILE_PATH,
                self::HASH,
                self::ORIGINAL_FILE_PATH
            )
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
            self::ORIGINAL_FILE_PATH
        );

        $expectedCustomAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tmp,
            self::HASH
        );

        $this->assertFileExists($expectedCustomAutoloadFilePath);
        $this->assertStringContainsString(
            'IncludeInterceptor.php',
            file_get_contents($expectedCustomAutoloadFilePath)
        );
    }

    public function coverageTestsProvider(): Generator
    {
        yield [
            [
                CoverageLineData::with(
                    'SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage with data set #5',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                    0.086178
                ),
                CoverageLineData::with(
                    'SimpleHabits\\Domain\\Model\\Goal\\GoalTest::it_calculates_percentage with data set #6',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
                    0.086178
                ),
                CoverageLineData::with(
                    'SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest::it_correctly_returns_id',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                    0.035935
                ),
                CoverageLineData::with(
                    'SimpleHabits\\Domain\\Model\\Goal\\GoalStepTest::it_correctly_returns_recorded_at_date',
                    '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                    0.035935
                ),
            ],
            [
                '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalStepTest.php',
                '/path/to/siteSimpleHabits/Domain/Model/Goal/GoalTest.php',
            ],
        ];

        yield [
            [
                CoverageLineData::with(
                    'Path\\To\\A::test_a',
                    '/path/to/A.php',
                    0.186178
                ),
                CoverageLineData::with(
                    'Path\\To\\B::test_b',
                    '/path/to/B.php',
                    0.086178
                ),
                CoverageLineData::with(
                    'Path\\To\\C::test_c',
                    '/path/to/C.php',
                    0.016178
                ),
            ],
            [
                '/path/to/C.php',
                '/path/to/B.php',
                '/path/to/A.php',
            ],
        ];
    }

    private function queryXpath(string $xml, string $query)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        return (new DOMXPath($dom))->query($query);
    }

    private function createConfigBuilder(
        ?string $originalPhpUnitXmlConfigPath = null
    ): MutationConfigBuilder {
        $phpunitXmlPath = $originalPhpUnitXmlConfigPath ?: self::FIXTURES . '/phpunit.xml';

        $replacer = new PathReplacer(new Filesystem(), $this->projectPath);

        return new MutationConfigBuilder(
            $this->tmp,
            file_get_contents($phpunitXmlPath),
            new XmlConfigurationManipulator($replacer, ''),
            'project/dir',
            new JUnitTestCaseSorter()
        );
    }
}
