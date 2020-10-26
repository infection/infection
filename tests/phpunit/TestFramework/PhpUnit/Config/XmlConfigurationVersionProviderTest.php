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

use Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use Infection\TestFramework\SafeDOMXPath;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use function version_compare;

final class XmlConfigurationVersionProviderTest extends TestCase
{
    /**
     * @var XmlConfigurationVersionProvider
     */
    private $versionProvider;

    protected function setUp(): void
    {
        $this->versionProvider = new XmlConfigurationVersionProvider();
    }

    public function configurationsProvider()
    {
        yield from take($this->legacyConfigurationsProvider())
            ->map(static function (string $xml) {
                yield $xml => [
                    SafeDOMXPath::fromString($xml),
                    false,
                ];
            });

        yield from take($this->mainlineConfigurationsProvider())
            ->map(static function (string $xml) {
                yield $xml => [
                    SafeDOMXPath::fromString($xml),
                    true,
                ];
            });
    }

    /**
     * @dataProvider configurationsProvider
     */
    public function test_it_finds_correct_version(SafeDOMXPath $xPath, bool $mainline): void
    {
        $version = $this->versionProvider->provide($xPath);

        $this->assertSame($mainline, version_compare($version, '9.3', '>='));
    }

    protected function legacyConfigurationsProvider()
    {
        yield <<<'XML'
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
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <logging>
        <log type="coverage-html" target="/path/to/tmp"/>
    </logging>
</phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <filter>
        <whitelist>
            <directory>/src/</directory>
        </whitelist>
    </filter>
</phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit disableCodeCoverageIgnore="true"></phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit ignoreDeprecatedCodeUnitsFromCodeCoverage="true"></phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit></phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/6.0/phpunit.xsd">
</phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/schema/9.2.xsd">
</phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./phpunit.xsd">
</phpunit>
XML;
    }

    protected function mainlineConfigurationsProvider()
    {
        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.3/phpunit.xsd">
</phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
XML;

        yield <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <logging>
        <text outputFile="logfile.txt"/>
    </logging>
</phpunit>
XML;
    }
}
