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

namespace Infection\Tests\Configuration\Schema;

use Infection\Configuration\Options\InfectionOptions;
use Infection\Configuration\Options\LogsOptions;
use Infection\Configuration\Options\PhpStanOptions;
use Infection\Configuration\Options\PhpUnitOptions;
use Infection\Configuration\Options\SourceOptions;
use Infection\Configuration\Options\StrykerConfigOptions;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\Schema\SchemaConfigurationFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaConfigurationFactory::class)]
final class SchemaConfigurationFactoryFromOptionsTest extends TestCase
{
    private SchemaConfigurationFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new SchemaConfigurationFactory();
    }

    public function test_it_creates_schema_configuration_from_options(): void
    {
        $options = new InfectionOptions(
            source: new SourceOptions(
                directories: ['src', 'lib'],
                excludes: ['tests'],
            ),
            timeout: 20.0,
            threads: 4,
            logs: new LogsOptions(
                text: 'infection.log',
                html: 'report.html',
                github: true,
            ),
            tmpDir: '/tmp/infection',
            phpUnit: new PhpUnitOptions(
                configDir: '.',
                customPath: 'vendor/bin/phpunit',
            ),
            phpStan: new PhpStanOptions(
                configDir: 'config',
            ),
            minMsi: 80.0,
            minCoveredMsi: 90.0,
            mutators: ['@default' => true],
            testFramework: 'phpunit',
            bootstrap: 'tests/bootstrap.php',
        );

        $schema = $this->factory->createFromOptions('/path/to/config.json', $options);

        $this->assertInstanceOf(SchemaConfiguration::class, $schema);
        $this->assertSame('/path/to/config.json', $schema->getFile());
        $this->assertSame(20.0, $schema->getTimeout());
        $this->assertSame(4, $schema->getThreads());
        $this->assertSame(['src', 'lib'], $schema->getSource()->getDirectories());
        $this->assertSame(['tests'], $schema->getSource()->getExcludes());
        $this->assertSame('infection.log', $schema->getLogs()->getTextLogFilePath());
        $this->assertSame('report.html', $schema->getLogs()->getHtmlLogFilePath());
        $this->assertTrue($schema->getLogs()->getUseGitHubAnnotationsLogger());
        $this->assertSame('/tmp/infection', $schema->getTmpDir());
        $this->assertSame('.', $schema->getPhpUnit()->getConfigDir());
        $this->assertSame('vendor/bin/phpunit', $schema->getPhpUnit()->getCustomPath());
        $this->assertSame('config', $schema->getPhpStan()->getConfigDir());
        $this->assertSame(80.0, $schema->getMinMsi());
        $this->assertSame(90.0, $schema->getMinCoveredMsi());
        $this->assertSame(['@default' => true], $schema->getMutators());
        $this->assertSame('phpunit', $schema->getTestFramework());
        $this->assertSame('tests/bootstrap.php', $schema->getBootstrap());
    }

    public function test_it_converts_stryker_config_badge(): void
    {
        $options = new InfectionOptions(
            source: new SourceOptions(directories: ['src']),
            logs: new LogsOptions(
                stryker: new StrykerConfigOptions(badge: 'main'),
            ),
        );

        $schema = $this->factory->createFromOptions('/path/to/config.json', $options);

        $strykerConfig = $schema->getLogs()->getStrykerConfig();
        $this->assertNotNull($strykerConfig);
        $this->assertFalse($strykerConfig->isForFullReport());
        $this->assertTrue($strykerConfig->applicableForBranch('main'));
    }

    public function test_it_converts_stryker_config_report(): void
    {
        $options = new InfectionOptions(
            source: new SourceOptions(directories: ['src']),
            logs: new LogsOptions(
                stryker: new StrykerConfigOptions(report: 'develop'),
            ),
        );

        $schema = $this->factory->createFromOptions('/path/to/config.json', $options);

        $strykerConfig = $schema->getLogs()->getStrykerConfig();
        $this->assertNotNull($strykerConfig);
        $this->assertTrue($strykerConfig->isForFullReport());
        $this->assertTrue($strykerConfig->applicableForBranch('develop'));
    }

    public function test_it_handles_null_optional_objects(): void
    {
        $options = new InfectionOptions(
            source: new SourceOptions(directories: ['src']),
            logs: null,
            phpUnit: null,
            phpStan: null,
        );

        $schema = $this->factory->createFromOptions('/path/to/config.json', $options);

        $this->assertInstanceOf(SchemaConfiguration::class, $schema);
        $this->assertNull($schema->getLogs()->getTextLogFilePath());
        $this->assertNull($schema->getPhpUnit()->getConfigDir());
        $this->assertNull($schema->getPhpStan()->getConfigDir());
    }
}
