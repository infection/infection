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

namespace Infection\Tests\Configuration\Options;

use Infection\Configuration\Options\InfectionConfigDeserializer;
use Infection\Configuration\Options\InfectionOptions;
use Infection\Configuration\Options\SerializerBuilder;
use JMS\Serializer\SerializerBuilder as JMSSerializerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InfectionConfigDeserializer::class)]
final class InfectionConfigDeserializerTest extends TestCase
{
    private InfectionConfigDeserializer $deserializer;

    protected function setUp(): void
    {
        $builder = new SerializerBuilder(JMSSerializerBuilder::create());

        $this->deserializer = new InfectionConfigDeserializer($builder->build());
    }

    public function test_deserialize_minimal_config(): void
    {
        $json = <<<'JSON'
            {
                "source": {
                    "directories": ["src"]
                }
            }
            JSON;

        $options = $this->deserializer->deserialize($json);

        $this->assertInstanceOf(InfectionOptions::class, $options);
        $this->assertSame(['src'], $options->source->directories);
        $this->assertSame([], $options->source->excludes);
        $this->assertSame(10.0, $options->timeout);
        $this->assertSame(1, $options->threads);
        $this->assertSame(['@default' => true], $options->mutators);
        $this->assertSame('phpunit', $options->testFramework);
    }

    public function test_deserialize_full_config(): void
    {
        $json = <<<'JSON'
            {
                "timeout": 10,
                "threads": 4,
                "source": {
                    "directories": ["src", "lib"],
                    "excludes": ["tests"]
                },
                "logs": {
                    "text": "infection.log",
                    "html": "infection-report.html",
                    "github": true
                },
                "tmpDir": "/tmp/infection",
                "phpUnit": {
                    "configDir": ".",
                    "customPath": "vendor/bin/phpunit"
                },
                "phpStan": {
                    "configDir": ".",
                    "customPath": "vendor/bin/phpstan"
                },
                "ignoreMsiWithNoMutations": true,
                "minMsi": 80.5,
                "minCoveredMsi": 90.0,
                "mutators": {
                    "@default": true,
                    "TrueValue": {
                        "ignore": ["src/SomeClass.php"],
                        "ignoreSourceCodeByRegex": [".*deprecated.*"]
                    }
                },
                "testFramework": "phpunit",
                "bootstrap": "tests/bootstrap.php"
            }
            JSON;

        $options = $this->deserializer->deserialize($json);

        $this->assertInstanceOf(InfectionOptions::class, $options);
        $this->assertSame(10.0, $options->timeout);
        $this->assertSame(4, $options->threads);
        $this->assertSame(['src', 'lib'], $options->source->directories);
        $this->assertSame(['tests'], $options->source->excludes);
        $this->assertSame('infection.log', $options->logs->text);
        $this->assertSame('infection-report.html', $options->logs->html);
        $this->assertTrue($options->logs->github);
        $this->assertSame('/tmp/infection', $options->tmpDir);
        $this->assertSame('.', $options->phpUnit->configDir);
        $this->assertSame('vendor/bin/phpunit', $options->phpUnit->customPath);
        $this->assertSame('.', $options->phpStan->configDir);
        $this->assertSame('vendor/bin/phpstan', $options->phpStan->customPath);
        $this->assertTrue($options->ignoreMsiWithNoMutations);
        $this->assertSame(80.5, $options->minMsi);
        $this->assertSame(90.0, $options->minCoveredMsi);
        $this->assertArrayHasKey('@default', $options->mutators);
        $this->assertTrue($options->mutators['@default']);
        $this->assertArrayHasKey('TrueValue', $options->mutators);
        $this->assertSame('phpunit', $options->testFramework);
        $this->assertSame('tests/bootstrap.php', $options->bootstrap);
    }

    public function test_deserialize_threads_as_max(): void
    {
        $json = <<<'JSON'
            {
                "source": {
                    "directories": ["src"]
                },
                "threads": "max"
            }
            JSON;

        $options = $this->deserializer->deserialize($json);

        $this->assertSame('max', $options->threads);
    }

    public function test_deserialize_with_stryker_badge(): void
    {
        $json = <<<'JSON'
            {
                "source": {
                    "directories": ["src"]
                },
                "logs": {
                    "stryker": {
                        "badge": "main"
                    }
                }
            }
            JSON;

        $options = $this->deserializer->deserialize($json);

        $this->assertNotNull($options->logs);
        $this->assertNotNull($options->logs->stryker);
        $this->assertSame('main', $options->logs->stryker->badge);
        $this->assertNull($options->logs->stryker->report);
    }

    public function test_deserialize_with_stryker_report(): void
    {
        $json = <<<'JSON'
            {
                "source": {
                    "directories": ["src"]
                },
                "logs": {
                    "stryker": {
                        "report": "develop"
                    }
                }
            }
            JSON;

        $options = $this->deserializer->deserialize($json);

        $this->assertNotNull($options->logs);
        $this->assertNotNull($options->logs->stryker);
        $this->assertNull($options->logs->stryker->badge);
        $this->assertSame('develop', $options->logs->stryker->report);
    }
}
