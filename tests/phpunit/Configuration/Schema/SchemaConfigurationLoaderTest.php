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

use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Configuration\Schema\SchemaConfigurationFileLoader;
use Infection\Configuration\Schema\SchemaConfigurationLoader;
use Infection\FileSystem\Locator\Locator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SchemaConfigurationLoaderTest extends TestCase
{
    /**
     * @var Locator|MockObject
     */
    private $locatorStub;

    /**
     * @var SchemaConfigurationFileLoader|MockObject
     */
    private $configFileLoaderStub;

    /**
     * @var SchemaConfigurationLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->locatorStub = $this->createMock(Locator::class);
        $this->configFileLoaderStub = $this->createMock(SchemaConfigurationFileLoader::class);

        $this->loader = new SchemaConfigurationLoader(
            $this->locatorStub,
            $this->configFileLoaderStub
        );
    }

    /**
     * @dataProvider configurationPathsProvider
     *
     * @param string[] $potentialPaths
     */
    public function test_it_loads_the_located_file(
        array $potentialPaths,
        string $expectedPath,
        SchemaConfiguration $expectedConfig
    ): void {
        $this->locatorStub
            ->expects($this->once())
            ->method('locateOneOf')
            ->with($potentialPaths)
            ->willReturn($expectedPath)
        ;

        $this->configFileLoaderStub
            ->expects($this->once())
            ->method('loadFile')
            ->with($expectedPath)
            ->willReturn($expectedConfig)
        ;

        $actualConfig = $this->loader->loadConfiguration($potentialPaths);

        $this->assertSame($expectedConfig, $actualConfig);
    }

    public function configurationPathsProvider(): iterable
    {
        $config = (new ReflectionClass(SchemaConfiguration::class))->newInstanceWithoutConstructor();

        yield 'first potential path' => [
            [
                '/path/to/configA',
                '/path/to/configB',
                '/path/to/configC',
            ],
            '/path/to/configA',
            $config,
        ];

        yield 'second potential path' => [
            [
                '/path/to/configA',
                '/path/to/configB',
                '/path/to/configC',
            ],
            '/path/to/configB',
            $config,
        ];

        yield 'empty values' => [
            [],
            '',
            $config,
        ];
    }
}
