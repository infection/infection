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

namespace Infection\Tests\TestFramework\PhpUnit\Config;

use Closure;
use Infection\TestFramework\PhpUnit\Config\InvalidPhpUnitConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\XML\SafeDOMXPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(XmlConfigurationManipulator::class)]
final class XmlConfigurationManipulatorTest extends TestCase
{
    private XmlConfigurationManipulator $configManipulator;

    protected function setUp(): void
    {
        $this->configManipulator = new XmlConfigurationManipulator(
            new PathReplacer(new Filesystem()),
        );
    }

    public function test_it_replaces_with_absolute_paths(): void
    {
        $this->assertItChangesPrePHPUnit93Configuration(
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->replaceWithAbsolutePaths($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="/app/autoload2.php"
                    colors="true"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    syntaxCheck="false"
                >
                    <testsuites>
                        <testsuite name="Application Test Suite">
                            <directory>/*Bundle</directory>
                            <exclude>/*Bundle/Fixtures</exclude>
                        </testsuite>
                    </testsuites>
                    <filter>
                        <whitelist>
                            <directory>/src/</directory>
                        </whitelist>
                    </filter>
                    <logging>
                        <log type="coverage-html" target="/path/to/tmp"/>
                    </logging>
                </phpunit>
                XML,
        );
    }

    public function test_it_replaces_with_absolute_paths_xml_file_with_tabs(): void
    {
        $this->assertItChangesXML(
            <<<'XML'
                <phpunit cacheTokens="true">
                    <testsuites>
                		<testsuite name="All Tests">
                			<directory suffix="UnitTest.php">
                				./Tests
                			</directory>
                		</testsuite>
                	</testsuites>
                </phpunit>
                XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->replaceWithAbsolutePaths($xPath);
            },
            <<<'XML'
                <phpunit cacheTokens="true">
                  <testsuites>
                    <testsuite name="All Tests">
                      <directory suffix="UnitTest.php">/Tests</directory>
                    </testsuite>
                  </testsuites>
                </phpunit>
                XML,
        );
    }

    public function test_it_removes_existing_loggers_from_pre_93_configuration(): void
    {
        $this->assertItChangesPrePHPUnit93Configuration(
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->removeExistingLoggers($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    colors="true"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    syntaxCheck="false"
                >
                    <testsuites>
                        <testsuite name="Application Test Suite">
                            <directory>./*Bundle</directory>
                            <exclude>./*Bundle/Fixtures</exclude>
                        </testsuite>
                    </testsuites>
                    <filter>
                        <whitelist>
                            <directory>src/</directory>
                        </whitelist>
                    </filter>
                </phpunit>
                XML,
        );
    }

    public function test_it_adds_coverage_whitelist_directories_to_pre_93_configuration(): void
    {
        $this->assertItChangesXML(
            <<<'XML'
                <phpunit cacheTokens="true">
                </phpunit>
                XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->addOrUpdateLegacyCoverageWhitelistNodes($xPath, ['src/', 'examples/'], []);
            },
            <<<'XML'
                <phpunit cacheTokens="true">
                  <filter>
                    <whitelist>
                      <directory>src/</directory>
                      <directory>examples/</directory>
                    </whitelist>
                  </filter>
                </phpunit>
                XML,
        );
    }

    public function test_it_adds_coverage_whitelist_files_to_pre_93_configuration(): void
    {
        $this->assertItChangesXML(
            <<<'XML'
                <phpunit cacheTokens="true">
                </phpunit>
                XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->addOrUpdateLegacyCoverageWhitelistNodes(
                    $xPath,
                    ['src/', 'examples/'],
                    ['src/File1.php', 'example/File2.php'],
                );
            },
            <<<'XML'
                <phpunit cacheTokens="true">
                  <filter>
                    <whitelist>
                      <file>src/File1.php</file>
                      <file>example/File2.php</file>
                    </whitelist>
                  </filter>
                </phpunit>
                XML,
        );
    }

    public function test_it_adds_coverage_whitelist_directories_to_post_93_configuration(): void
    {
        $this->assertItChangesXML(
            <<<'XML'
                <phpunit cacheTokens="true">
                </phpunit>
                XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->addOrUpdateCoverageIncludeNodes($xPath, ['src/', 'examples/'], []);
            },
            <<<'XML'
                <phpunit cacheTokens="true">
                  <coverage>
                    <include>
                      <directory>src/</directory>
                      <directory>examples/</directory>
                    </include>
                  </coverage>
                </phpunit>
                XML,
        );
    }

    public function test_it_adds_coverage_whitelist_files_to_post_93_configuration(): void
    {
        $this->assertItChangesXML(
            <<<'XML'
                <phpunit cacheTokens="true">
                </phpunit>
                XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->addOrUpdateCoverageIncludeNodes($xPath,
                    ['src/', 'examples/'],
                    ['src/File1.php', 'example/File2.php'],
                );
            },
            <<<'XML'
                <phpunit cacheTokens="true">
                  <coverage>
                    <include>
                      <file>src/File1.php</file>
                      <file>example/File2.php</file>
                    </include>
                  </coverage>
                </phpunit>
                XML,
        );
    }

    public function test_it_adds_source_include_directories_to_post_10_1_configuration(): void
    {
        $this->assertItChangesXML(
            <<<'XML'
                <phpunit cacheTokens="true">
                </phpunit>
                XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->addOrUpdateSourceIncludeNodes($xPath, ['src/', 'examples/'], []);
            },
            <<<'XML'
                <phpunit cacheTokens="true">
                  <source>
                    <include>
                      <directory>src/</directory>
                      <directory>examples/</directory>
                    </include>
                  </source>
                </phpunit>
                XML,
        );
    }

    public function test_it_removes_existing_loggers_from_post_93_configuration(): void
    {
        $this->assertItChangesPostPHPUnit93Configuration(
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->removeExistingLoggers($xPath);
            },
            <<<'XML_WRAP'
                <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.3/phpunit.xsd">
                  <coverage disableCodeCoverageIgnore="true" ignoreDeprecatedCodeUnits="true" includeUncoveredFiles="true" processUncoveredFiles="true">
                    <include>
                      <directory suffix=".php">src</directory>
                    </include>
                    <exclude>
                      <directory suffix=".php">src/generated</directory>
                      <file>src/autoload.php</file>
                    </exclude>
                  </coverage>
                </phpunit>
                XML_WRAP,
        );
    }

    public function test_it_sets_set_stop_on_failure(): void
    {
        $this->assertItChangesPrePHPUnit93Configuration(
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->setStopOnFailureOrDefect('9.3', $xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    colors="true"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    stopOnFailure="true"
                    syntaxCheck="false"
                >
                    <testsuites>
                        <testsuite name="Application Test Suite">
                            <directory>./*Bundle</directory>
                            <exclude>./*Bundle/Fixtures</exclude>
                        </testsuite>
                    </testsuites>
                    <filter>
                        <whitelist>
                            <directory>src/</directory>
                        </whitelist>
                    </filter>
                    <logging>
                        <log type="coverage-html" target="/path/to/tmp"/>
                    </logging>
                </phpunit>
                XML,
        );
    }

    public function test_it_sets_set_stop_on_failure_when_it_is_already_present(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                backupGlobals="false"
                backupStaticAttributes="false"
                bootstrap="app/autoload2.php"
                colors="true"
                convertErrorsToExceptions="true"
                convertNoticesToExceptions="true"
                convertWarningsToExceptions="true"
                printerClass="Fake\Printer\Class"
                processIsolation="false"
                stopOnFailure="false"
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->setStopOnFailureOrDefect('9.3', $xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    colors="true"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    stopOnFailure="true"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_sets_set_stop_on_defect_when_it_is_already_present_10_0(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                backupGlobals="false"
                backupStaticAttributes="false"
                bootstrap="app/autoload2.php"
                colors="true"
                convertErrorsToExceptions="true"
                convertNoticesToExceptions="true"
                convertWarningsToExceptions="true"
                printerClass="Fake\Printer\Class"
                processIsolation="false"
                stopOnDefect="false"
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->setStopOnFailureOrDefect('10.0', $xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    colors="true"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    stopOnDefect="true"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_deactivates_colors(): void
    {
        $this->assertItChangesPrePHPUnit93Configuration(
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->deactivateColours($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    colors="false"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    syntaxCheck="false"
                >
                    <testsuites>
                        <testsuite name="Application Test Suite">
                            <directory>./*Bundle</directory>
                            <exclude>./*Bundle/Fixtures</exclude>
                        </testsuite>
                    </testsuites>
                    <filter>
                        <whitelist>
                            <directory>src/</directory>
                        </whitelist>
                    </filter>
                    <logging>
                        <log type="coverage-html" target="/path/to/tmp"/>
                    </logging>
                </phpunit>
                XML,
        );
    }

    public function test_it_deactivates_colors_when_it_is_not_already_present(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                backupGlobals="false"
                backupStaticAttributes="false"
                bootstrap="app/autoload2.php"
                convertErrorsToExceptions="true"
                convertNoticesToExceptions="true"
                convertWarningsToExceptions="true"
                printerClass="Fake\Printer\Class"
                processIsolation="false"
                stopOnFailure="false"
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->deactivateColours($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    colors="false"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    stopOnFailure="false"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_sets_cache_result_to_false_when_it_exists(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                backupGlobals="false"
                backupStaticAttributes="false"
                bootstrap="app/autoload2.php"
                cacheResult="true"
                convertErrorsToExceptions="true"
                convertNoticesToExceptions="true"
                convertWarningsToExceptions="true"
                printerClass="Fake\Printer\Class"
                processIsolation="false"
                stopOnFailure="false"
                syntaxCheck="false"
            >
            </phpunit>

            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->deactivateResultCaching($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    cacheResult="false"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    stopOnFailure="false"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_sets_cache_result_to_false_when_it_does_not_exist(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                backupGlobals="false"
                backupStaticAttributes="false"
                bootstrap="app/autoload2.php"
                convertErrorsToExceptions="true"
                convertNoticesToExceptions="true"
                convertWarningsToExceptions="true"
                printerClass="Fake\Printer\Class"
                processIsolation="false"
                stopOnFailure="false"
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->deactivateResultCaching($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    cacheResult="false"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    printerClass="Fake\Printer\Class"
                    processIsolation="false"
                    stopOnFailure="false"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_sets_stderr_to_false_when_it_exists(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                stderr="true"
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->deactivateStderrRedirection($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    stderr="false"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_sets_stderr_to_false_when_it_does_not_exist(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->deactivateStderrRedirection($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    stderr="false"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_activates_result_cache_and_execution_order_defects_for_phpunit_11_0(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->handleResultCacheAndExecutionOrder('11.0', $xPath, 'a1b2c3', '/tmp');
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    executionOrder="defects"
                    cacheResult="true"
                    cacheDirectory="/tmp/.phpunit.result.cache.a1b2c3"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_activates_result_cache_and_execution_order_defects_for_phpunit_7_3(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->handleResultCacheAndExecutionOrder('7.3', $xPath, 'a1b2c3', '/tmp');
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    executionOrder="defects"
                    cacheResult="true"
                    cacheResultFile=".phpunit.result.cache.a1b2c3"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_does_not_set_result_cache_for_phpunit_7_1(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->handleResultCacheAndExecutionOrder('7.1', $xPath, 'a1b2c3', '/tmp');
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_removes_existing_printers(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                backupGlobals="false"
                backupStaticAttributes="false"
                bootstrap="app/autoload2.php"
                convertErrorsToExceptions="true"
                convertNoticesToExceptions="true"
                convertWarningsToExceptions="true"
                printerClass="Fake\Printer\Class"
                processIsolation="false"
                stopOnFailure="false"
                syntaxCheck="false"
            >
            </phpunit>
            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->removeExistingPrinters($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    backupGlobals="false"
                    backupStaticAttributes="false"
                    bootstrap="app/autoload2.php"
                    convertErrorsToExceptions="true"
                    convertNoticesToExceptions="true"
                    convertWarningsToExceptions="true"
                    processIsolation="false"
                    stopOnFailure="false"
                    syntaxCheck="false"
                >
                </phpunit>
                XML,
        );
    }

    public function test_it_cannot_validate_invalid_phpunit_xml_configuration(): void
    {
        $xPath = $this->createXPath('<invalid></invalid>');

        $this->expectException(InvalidPhpUnitConfiguration::class);
        $this->expectExceptionMessage('The file "/path/to/phpunit.xml" is not a valid PHPUnit configuration file');

        $this->configManipulator->validate('/path/to/phpunit.xml', $xPath);
    }

    public function test_it_consider_as_valid_a_phpunit_xml_configuration_without_xsd(): void
    {
        $xPath = $this->createXPath(<<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit>
            </phpunit>
            XML
        );

        $this->assertTrue($this->configManipulator->validate('/path/to/phpunit.xml', $xPath));
    }

    public function test_it_works_if_schema_location_is_absent_but_xmlns_xsi_is_present(): void
    {
        $xPath = $this->createXPath(<<<XML_WRAP
            <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    bootstrap="vendor/autoload.php"
                    colors="true">
            </phpunit>
            XML_WRAP
        );

        $this->assertTrue($this->configManipulator->validate('/path/to/phpunit.xml', $xPath));
    }

    public function test_it_removes_default_test_suite(): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                defaultTestSuite="unit"
                printerClass="Fake\Printer\Class"
                stopOnFailure="false"
                syntaxCheck="false"
            >
            </phpunit>

            XML,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->removeDefaultTestSuite($xPath);
            },
            <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <phpunit
                    printerClass="Fake\Printer\Class"
                    stopOnFailure="false"
                    syntaxCheck="false"
                >
                </phpunit>

                XML,
        );
    }

    private function assertItChangesPostPHPUnit93Configuration(Closure $changeXml, string $expectedXml): void
    {
        $this->assertItChangesXML(<<<'XML_WRAP'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.3/phpunit.xsd">

                <coverage includeUncoveredFiles="true"
                          processUncoveredFiles="true"
                          ignoreDeprecatedCodeUnits="true"
                          disableCodeCoverageIgnore="true">
                    <include>
                        <directory suffix=".php">src</directory>
                    </include>

                    <exclude>
                        <directory suffix=".php">src/generated</directory>
                        <file>src/autoload.php</file>
                    </exclude>

                    <report>
                        <clover outputFile="clover.xml"/>
                        <crap4j outputFile="crap4j.xml" threshold="50"/>
                        <html outputDirectory="html-coverage" lowUpperBound="50" highLowerBound="90"/>
                        <php outputFile="coverage.php"/>
                        <text outputFile="coverage.txt" showUncoveredFiles="false" showOnlySummary="true"/>
                        <xml outputDirectory="xml-coverage"/>
                    </report>
                </coverage>

                <logging>
                    <junit outputFile="junit.xml"/>
                    <teamcity outputFile="teamcity.txt"/>
                    <testdoxHtml outputFile="testdox.html"/>
                    <testdoxText outputFile="testdox.txt"/>
                    <testdoxXml outputFile="testdox.xml"/>
                    <text outputFile="logfile.txt"/>
                </logging>
            </phpunit>
            XML_WRAP,
            $changeXml,
            $expectedXml,
        );
    }

    private function assertItChangesPrePHPUnit93Configuration(Closure $changeXml, string $expectedXml): void
    {
        $this->assertItChangesXML(<<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit
                backupGlobals="false"
                backupStaticAttributes="false"
                bootstrap="app/autoload2.php"
                colors="true"
                convertErrorsToExceptions="true"
                convertNoticesToExceptions="true"
                convertWarningsToExceptions="true"
                printerClass="Fake\Printer\Class"
                processIsolation="false"
                syntaxCheck="false"
            >
                <testsuites>
                    <testsuite name="Application Test Suite">
                        <directory>./*Bundle</directory>
                        <exclude>./*Bundle/Fixtures</exclude>
                    </testsuite>
                </testsuites>

                <filter>
                    <whitelist>
                        <directory>src/</directory>
                    </whitelist>
                </filter>

                <logging>
                    <log type="coverage-html" target="/path/to/tmp"/>
                </logging>
            </phpunit>
            XML,
            $changeXml,
            $expectedXml,
        );
    }

    private function assertItChangesXML(string $inputXml, Closure $changeXml, string $expectedXml): void
    {
        $xPath = $this->createXPath($inputXml);

        $changeXml($this->configManipulator, $xPath);

        $actualXml = $xPath->document->saveXML();

        $this->assertNotFalse($actualXml);
        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }

    private function createXPath(string $xml): SafeDOMXPath
    {
        return SafeDOMXPath::fromString(
            $xml,
            preserveWhiteSpace: false,
            formatOutput: true,
        );
    }
}
