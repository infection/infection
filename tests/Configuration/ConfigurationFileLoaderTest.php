<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use Infection\Configuration\Configuration;
use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\ConfigurationFileLoader;
use Infection\Configuration\RawConfiguration\RawConfiguration;
use Infection\Configuration\Schema\SchemaValidator;
use Infection\Console\InfectionContainer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Argument\Token\TokenInterface;
use Prophecy\Argument\Token\TypeToken;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;

class ConfigurationFileLoaderTest extends TestCase
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

    public function setUp(): void
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
        $path = '/path/to/config';
        $expectedConfig = (new ReflectionClass(Configuration::class))->newInstanceWithoutConstructor();

        $this->schemaValidatorProphecy
            ->validate(self::createRawConfigWithPathArgument($path))
            ->shouldBeCalled()
        ;

        $this->configFactoryProphecy
            ->create(self::createRawConfigWithPathArgument($path))
            ->willReturn($expectedConfig)
        ;

        $actual = $this->loader->loadFile($path);

        $this->assertSame($expectedConfig, $actual);

        $this->schemaValidatorProphecy->validate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $this->configFactoryProphecy->create(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    private static function createRawConfigWithPathArgument(string $path): TokenInterface
    {
        return Argument::that(function (RawConfiguration $config) use ($path) {
            self::assertSame($path, $config->getPath());

            return new TypeToken(RawConfiguration::class);
        });
    }
}
