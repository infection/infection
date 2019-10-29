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

namespace Infection\Tests\Configuration;

use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\ConfigurationFileLoader;
use Infection\Configuration\RawConfiguration\RawConfiguration;
use Infection\Configuration\Schema\SchemaValidator;
use Infection\Configuration\SchemaConfiguration;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Argument\Token\TokenInterface;
use Prophecy\Argument\Token\TypeToken;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;
use function Safe\realpath;

final class ConfigurationFileLoaderTest extends TestCase
{
    /**
     * @var SchemaValidator&ObjectProphecy
     */
    private $schemaValidatorProphecy;

    /**
     * @var ConfigurationFactory&ObjectProphecy
     */
    private $configFactoryProphecy;

    /**
     * @var ConfigurationFileLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->schemaValidatorProphecy = $this->prophesize(SchemaValidator::class);
        $this->configFactoryProphecy = $this->prophesize(ConfigurationFactory::class);

        $this->loader = new ConfigurationFileLoader(
            $this->schemaValidatorProphecy->reveal(),
            $this->configFactoryProphecy->reveal()
        );
    }

    public function test_it_create_a_configuration_from_a_file_path(): void
    {
        $path = realpath(__DIR__.'/../Fixtures/Configuration/file.json');
        $decodedContents = (object) ['foo' => 'bar'];
        $expectedConfig = (new ReflectionClass(SchemaConfiguration::class))->newInstanceWithoutConstructor();

        $this->schemaValidatorProphecy
            ->validate(self::createRawConfigWithPathArgument($path))
            ->shouldBeCalled()
        ;

        $this->configFactoryProphecy
            ->create($path, $decodedContents)
            ->willReturn($expectedConfig)
        ;

        $actual = $this->loader->loadFile($path);

        $this->assertSame($expectedConfig, $actual);

        $this->schemaValidatorProphecy->validate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $this->configFactoryProphecy->create(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    private static function createRawConfigWithPathArgument(string $path): TokenInterface
    {
        return Argument::that(static function (RawConfiguration $config) use ($path) {
            self::assertSame($path, $config->getPath());

            return new TypeToken(RawConfiguration::class);
        });
    }
}
