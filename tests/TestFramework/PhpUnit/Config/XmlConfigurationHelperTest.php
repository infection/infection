<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Config;

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

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer());

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

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer());

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

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer());

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

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer());

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

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer());

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

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer());

        $xmlconfig->deactivateColours($xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" printerClass="Fake\Printer\Class" stopOnFailure="false" colors="false">
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

        $xmlconfig = new XmlConfigurationHelper($this->getPathReplacer());

        $xmlconfig->removeExistingPrinters($dom, $xPath);

        $this->assertSame(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false" backupStaticAttributes="false" bootstrap="app/autoload2.php" convertErrorsToExceptions="true" convertNoticesToExceptions="true" convertWarningsToExceptions="true" processIsolation="false" syntaxCheck="false" stopOnFailure="false">
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
