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
use Infection\Configuration\Schema\SchemaConfigurationFactory;
use Infection\Configuration\Schema\SchemaConfigurationFile;
use Infection\Configuration\Schema\SchemaConfigurationFileLoader;
use Infection\Configuration\Schema\SchemaValidator;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\realpath;

class SchemaConfigurationFileLoaderTest extends TestCase
{
    /**
     * @var SchemaValidator|MockObject
     */
    private $schemaValidatorStub;

    /**
     * @var SchemaConfigurationFactory|MockObject
     */
    private $configFactoryStub;

    /**
     * @var SchemaConfigurationFileLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->schemaValidatorStub = $this->createMock(SchemaValidator::class);
        $this->configFactoryStub = $this->createMock(SchemaConfigurationFactory::class);

        $this->loader = new SchemaConfigurationFileLoader(
            $this->schemaValidatorStub,
            $this->configFactoryStub
        );
    }

    public function test_it_create_a_configuration_from_a_file_path(): void
    {
        $path = realpath(__DIR__ . '/../../Fixtures/Configuration/file.json');
        $decodedContents = (object) ['foo' => 'bar'];
        $expectedConfig = (new ReflectionClass(SchemaConfiguration::class))->newInstanceWithoutConstructor();

        $this->schemaValidatorStub
            ->expects($this->once())
            ->method('validate')
            ->with(self::createRawConfigWithPathArgument($path))
        ;

        $this->configFactoryStub
            ->expects($this->once())
            ->method('create')
            ->with($path, $decodedContents)
            ->willReturn($expectedConfig)
        ;

        $actual = $this->loader->loadFile($path);

        $this->assertSame($expectedConfig, $actual);
    }

    private static function createRawConfigWithPathArgument(string $path): Constraint
    {
        return new Callback(static function (SchemaConfigurationFile $config) use ($path) {
            self::assertSame($path, $config->getPath());

            return true;
        });
    }
}
