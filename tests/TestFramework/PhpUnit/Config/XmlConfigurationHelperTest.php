<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

use Infection\TestFramework\PhpUnit\Config\Exception\InvalidPhpUnitXmlConfigException;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class XmlConfigurationHelperTest extends TestCase
{
    public function test_it_replaces_with_absolute_paths(): void
    {
        $dom = $this->getDomDocument();
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->replaceWithAbsolutePaths($xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="/app/autoload2.php" colors="true" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class">
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
            , $dom->saveXML()
        );
    }

    public function test_it_removes_existing_loggers(): void
    {
        $dom = $this->getDomDocument();
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->removeExistingLoggers($dom, $xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" colors="true" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class">
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
            , $dom->saveXML()
        );
    }

    public function test_it_sets_set_stop_on_failure(): void
    {
        $dom = $this->getDomDocument();
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->setStopOnFailure($xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" colors="true" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="true">
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
            , $dom->saveXML()
        );
    }

    public function test_it_sets_set_stop_on_failure_when_it_is_already_present(): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" colors="true" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false">
</phpunit>

XML
        );
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->setStopOnFailure($xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" colors="true" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="true">
</phpunit>

XML
            , $dom->saveXML()
        );
    }

    public function test_it_deactivates_colors(): void
    {
        $dom = $this->getDomDocument();
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->deactivateColours($xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" colors="false" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class">
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
            , $dom->saveXML()
        );
    }

    public function test_it_deactivates_colors_when_it_is_not_already_present(): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false">
</phpunit>

XML
        );
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->deactivateColours($xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false" colors="false">
</phpunit>

XML
            , $dom->saveXML()
        );
    }

    public function test_it_removes_cache_result_file(): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" cacheResultFile="phpunit.cache" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false">
</phpunit>

XML
        );
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->removeCacheResultFile($dom, $xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false">
</phpunit>

XML
            , $dom->saveXML()
        );
    }

    public function test_it_removes_existing_printers(): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false">
</phpunit>

XML
        );
        $xPath = new \DOMXPath($dom);

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->removeExistingPrinters($dom, $xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" stopOnFailure="false">
</phpunit>

XML
            , $dom->saveXML()
        );
    }

    public function test_it_validates_xml_by_root_node(): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML('<invalid></invalid>');
        $xPath = new \DOMXPath($dom);
        $xmlHelper = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $this->expectException(InvalidPhpUnitXmlConfigException::class);
        $this->expectExceptionMessage('phpunit.xml does not contain a valid PHPUnit configuration.');

        $xmlHelper->validate($dom, $xPath);
    }

    /**
     * @dataProvider schemaProvider
     */
    public function test_it_validates_xml_by_xsd(string $xsdSchema): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<"XML"
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xsi:noNamespaceSchemaLocation="$xsdSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <invalid></invalid>
</phpunit>

XML
        );
        $xPath = new \DOMXPath($dom);
        $xmlHelper = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $this->expectException(InvalidPhpUnitXmlConfigException::class);
        $this->expectExceptionMessageRegExp('/Element \'invalid\'\: This element is not expected/');

        $xmlHelper->validate($dom, $xPath);
    }

    public function schemaProvider(): \Generator
    {
        yield 'Remote XSD' => ['https://schema.phpunit.de/6.1/phpunit.xsd'];

        yield 'Local XSD' => ['./vendor/phpunit/phpunit/phpunit.xsd'];
    }

    /**
     * @dataProvider schemaProvider
     */
    public function test_it_passes_validation_by_xsd(string $xsdSchema): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xsi:noNamespaceSchemaLocation="$xsdSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
</phpunit>

XML
        );
        $xPath = new \DOMXPath($dom);

        $xmlHelper = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $this->assertTrue(true, $xmlHelper->validate($dom, $xPath));
    }

    public function test_it_removes_default_test_suite(): void
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false" defaultTestSuite="unit">
</phpunit>

XML
        );

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer(), '');

        $xmlconfig->removeDefaultTestSuite($dom, new \DOMXPath($dom));

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false">
</phpunit>

XML
            , $dom->saveXML()
        );
    }

    private function getDomDocument(): \DOMDocument
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->getPHPUnitXMLConfig());

        return $dom;
    }

    private function getPathReplacer(): PathReplacer
    {
        return new PathReplacer(new Filesystem());
    }

    private function getPHPUnitXMLConfig(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" colors="true" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class">
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

XML;
    }
}
