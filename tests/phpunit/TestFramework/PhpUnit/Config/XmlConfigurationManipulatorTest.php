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
use DOMDocument;
use const E_ALL;
use Infection\TestFramework\PhpUnit\Config\InvalidPhpUnitConfiguration;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\SafeDOMXPath;
use function Infection\Tests\normalizeLineReturn;
use InvalidArgumentException;
use const PHP_OS_FAMILY;
use PHPUnit\Framework\TestCase;
use function restore_error_handler;
use function Safe\sprintf;
use function set_error_handler;
use function str_replace;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

/**
 * @group integration
 */
final class XmlConfigurationManipulatorTest extends TestCase
{
    /**
     * @var XmlConfigurationManipulator
     */
    private $configManipulator;

    protected function setUp(): void
    {
        $this->configManipulator = new XmlConfigurationManipulator(
            new PathReplacer(new Filesystem()),
            ''
        );
    }

    public function test_it_replaces_with_absolute_paths(): void
    {
        $this->assertItChangesStandardConfiguration(
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
XML
        );
    }

    public function test_it_removes_existing_loggers(): void
    {
        $this->assertItChangesStandardConfiguration(
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
XML
        );
    }

    public function test_it_sets_set_stop_on_failure(): void
    {
        $this->assertItChangesStandardConfiguration(
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->setStopOnFailure($xPath);
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
XML
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
XML
            ,
            static function (XmlConfigurationManipulator $configManipulator, SafeDOMXPath $xPath): void {
                $configManipulator->setStopOnFailure($xPath);
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
XML
        );
    }

    public function test_it_deactivates_colors(): void
    {
        $this->assertItChangesStandardConfiguration(
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
XML
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
XML
            ,
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
XML
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

XML
            ,
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
XML
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
XML
            ,
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
XML
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
XML
            ,
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
XML
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
XML
            ,
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
XML
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
XML
            ,
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
XML
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

    /**
     * @dataProvider invalidSchemaProvider
     */
    public function test_it_cannot_validates_xml_if_schema_file_is_invalid(
        string $xsdSchema,
        string $errorMessage
    ): void {
        $xPath = $this->createXPath(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xsi:noNamespaceSchemaLocation="$xsdSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    foo="bar"
>
    <invalid></invalid>
</phpunit>
XML
        );

        set_error_handler(
            static function (int $type, string $message, string $file, string $line): void {
                // Silence!
            },
            E_ALL
        );

        try {
            $this->configManipulator->validate('/path/to/phpunit.xml', $xPath);

            $this->fail('Expected exception to be thrown');
        } catch (InvalidArgumentException | InvalidPhpUnitConfiguration $exception) {
            $this->assertSame(
                $errorMessage,
                normalizeLineReturn($exception->getMessage())
            );
        } finally {
            restore_error_handler();
        }
    }

    public function test_it_works_if_schema_location_is_absent_but_xmlns_xsi_is_present(): void
    {
        $xPath = $this->createXPath(<<<XML
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        bootstrap="vendor/autoload.php"
        colors="true">
</phpunit>
XML
        );

        $this->assertTrue($this->configManipulator->validate('/path/to/phpunit.xml', $xPath));
    }

    /**
     * @dataProvider schemaProvider
     *
     * @group integration Might require an external connection to download the XSD
     */
    public function test_it_validates_xml_by_xsd(string $xsdSchema): void
    {
        $xPath = $this->createXPath(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xsi:noNamespaceSchemaLocation="$xsdSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    foo="bar"
>
    <invalid></invalid>
</phpunit>
XML
        );

        try {
            $this->configManipulator->validate('/path/to/phpunit.xml', $xPath);

            $this->fail('Expected exception to be thrown');
        } catch (InvalidPhpUnitConfiguration $exception) {
            $infectionPath = sprintf(
                '%s%s',
                PHP_OS_FAMILY === 'Windows' ? 'file:/' : '',
                Path::canonicalize(__DIR__ . '/../../../../../')
            );

            $errorMessage = str_replace(
                $infectionPath,
                '/path/to/infection',
                normalizeLineReturn($exception->getMessage())
            );

            $this->assertSame(
                <<<'EOF'
The file "/path/to/phpunit.xml" does not pass the XSD schema validation.
[Error] Element 'phpunit', attribute 'foo': The attribute 'foo' is not allowed.
 in /path/to/infection/ (line 6, col 0)
[Error] Element 'invalid': This element is not expected.
 in /path/to/infection/ (line 7, col 0)

EOF
                ,
                $errorMessage
            );
        }
    }

    /**
     * @dataProvider schemaProvider
     *
     * @group integration Might require an external connection to download the XSD
     */
    public function test_it_passes_validation_by_xsd(string $xsdSchema): void
    {
        $xPath = $this->createXPath(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xsi:noNamespaceSchemaLocation="$xsdSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
>
</phpunit>
XML
        );

        $this->assertTrue($this->configManipulator->validate('/path/to/phpunit.xml', $xPath));
    }

    public function test_it_uses_the_configured_phpunit_config_dir_to_build_schema_paths(): void
    {
        $configManipulator = new XmlConfigurationManipulator(
            new PathReplacer(new Filesystem()),
            __DIR__ . '/../../../../..'
        );

        $xPath = $this->createXPath(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
>
</phpunit>
XML
        );

        $this->assertTrue($configManipulator->validate('/path/to/phpunit.xml', $xPath));
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

XML
            ,
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

XML
        );
    }

    public function schemaProvider(): iterable
    {
        yield 'remote XSD' => ['https://raw.githubusercontent.com/sebastianbergmann/phpunit/7.4.0/phpunit.xsd'];

        yield 'local XSD' => ['./vendor/phpunit/phpunit/phpunit.xsd'];
    }

    public function invalidSchemaProvider(): iterable
    {
        yield 'empty' => [
            '',
            'Invalid schema path found ""',
        ];

        yield 'invalid path' => [
            '/unknown/path/to/phpunit.xsd',
            'Invalid schema path found "/unknown/path/to/phpunit.xsd"',
        ];

        yield 'invalid URL' => [
            'https://unknown.com',
            <<<'EOF'
The file "/path/to/phpunit.xml" does not pass the XSD schema validation.
[Warning] failed to load external entity "https://unknown.com"

[Error] Failed to locate the main schema resource at 'https://unknown.com'.


EOF
            ,
        ];
    }

    private function assertItChangesStandardConfiguration(Closure $changeXml, string $expectedXml): void
    {
        $xPath = $this->createXPath(<<<'XML'
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
XML
        );

        $changeXml($this->configManipulator, $xPath);

        $actualXml = $xPath->document->saveXML();

        $this->assertNotFalse($actualXml);
        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
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
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);

        return new SafeDOMXPath($dom);
    }
}
