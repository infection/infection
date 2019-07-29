<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration;

use Generator;
use Infection\Configuration\Configuration;
use Infection\Configuration\ConfigurationFileLoader;
use Infection\Configuration\ConfigurationLoader;
use Infection\Locator\Locator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;

class ConfigurationLoaderTest extends TestCase
{
    /**
     * @var Locator&ObjectProphecy
     */
    private $locatorProphecy;

    /**
     * @var ConfigurationFileLoader&ObjectProphecy
     */
    private $configFileLoaderProphecy;

    /**
     * @var ConfigurationLoader
     */
    private $loader;

    public function setUp(): void
    {
        $this->locatorProphecy = $this->prophesize(Locator::class);
        $this->configFileLoaderProphecy = $this->prophesize(ConfigurationFileLoader::class);

        $this->loader = new ConfigurationLoader(
            $this->locatorProphecy->reveal(),
            $this->configFileLoaderProphecy->reveal()
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
        Configuration $expectedConfig
    ): void
    {
        $this->locatorProphecy
            ->locateOneOf($potentialPaths)
            ->willReturn($expectedPath)
        ;

        $this->configFileLoaderProphecy
            ->loadFile($expectedPath)
            ->willReturn($expectedConfig)
        ;

        $actualConfig = $this->loader->loadConfiguration($potentialPaths);

        $this->assertSame($expectedConfig, $actualConfig);

        $this->locatorProphecy->locateOneOf(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $this->configFileLoaderProphecy->loadFile(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function configurationPathsProvider(): Generator
    {
        $config = (new ReflectionClass(Configuration::class))->newInstanceWithoutConstructor();

        yield 'first potenal path' => [
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
