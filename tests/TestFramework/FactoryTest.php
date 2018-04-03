<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework;

use Infection\Config\InfectionConfig;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\Factory;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\Utils\VersionParser;
use Mockery;
use Symfony\Component\Filesystem\Filesystem;

class FactoryTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function test_it_throws_an_exception_if_it_cant_find_the_testframework()
    {
        $factory = new Factory(
            '',
            '',
            Mockery::mock(TestFrameworkConfigLocatorInterface::class),
            Mockery::mock(XmlConfigurationHelper::class),
            '',
            new InfectionConfig(new \stdClass(), new Filesystem(), ''),
            Mockery::mock(VersionParser::class)
        );

        $this->expectException(\InvalidArgumentException::class);
        $factory->create('Fake Test Framework', false);
    }
}
