<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Console;

use Infection\Config\InfectionConfig;
use Infection\Console\InfectionContainer;
use Mockery;

class InfectionContainerTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function test_it_is_a_usable_container()
    {
        $container = new InfectionContainer();
        $this->assertArrayHasKey('project.dir', $container);
    }

    public function test_it_provides_infection_config()
    {
        $container = new InfectionContainer();

        $container->setInfectionConfigInitializer(function () {
            $infectionConfigMock = Mockery::mock(InfectionConfig::class);
            $infectionConfigMock->shouldReceive('getTestFramework')->once()->andReturn('phpunit');

            return $infectionConfigMock;
        });

        $this->assertSame('phpunit', $container->getInfectionConfig()->getTestFramework());
    }
}
