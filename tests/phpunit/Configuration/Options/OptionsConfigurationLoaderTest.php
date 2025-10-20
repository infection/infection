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
use Infection\Configuration\Options\OptionsConfigurationLoader;
use Infection\Configuration\Options\SerializerBuilder;
use Infection\Configuration\Schema\SchemaValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\realpath;

#[Group('integration')]
#[CoversClass(OptionsConfigurationLoader::class)]
final class OptionsConfigurationLoaderTest extends TestCase
{
    private OptionsConfigurationLoader $loader;

    protected function setUp(): void
    {
        $builder = new SerializerBuilder();
        $deserializer = new InfectionConfigDeserializer($builder->build());
        $validator = new SchemaValidator();

        $this->loader = new OptionsConfigurationLoader($validator, $deserializer);
    }

    public function test_it_loads_real_infection_json5_file(): void
    {
        $path = realpath(__DIR__ . '/../../../../infection.json5');

        $options = $this->loader->load($path);

        $this->assertInstanceOf(InfectionOptions::class, $options);
        $this->assertSame(['src'], $options->source->directories);
        $this->assertSame(25.0, $options->timeout);
        $this->assertSame('max', $options->threads);
        $this->assertNotNull($options->logs);
        $this->assertNotNull($options->logs->stryker);
        $this->assertSame('master', $options->logs->stryker->report);
    }

    public function test_it_loads_e2e_infection_json_file(): void
    {
        $path = realpath(__DIR__ . '/../../../e2e/PHPUnit101/infection.json');

        $options = $this->loader->load($path);

        $this->assertInstanceOf(InfectionOptions::class, $options);
    }

    public function test_it_applies_file_values_and_defaults(): void
    {
        $path = realpath(__DIR__ . '/../../../e2e/PHPUnit101/infection.json');

        $options = $this->loader->load($path);

        // Values from config file override defaults
        $this->assertSame(25.0, $options->timeout);
        $this->assertSame(['src'], $options->source->directories);
        $this->assertSame('.', $options->tmpDir);
        $this->assertSame(['PublicVisibility' => true], $options->mutators);

        // Defaults for properties not in config
        $this->assertSame(1, $options->threads);
        $this->assertSame('phpunit', $options->testFramework);
    }
}
