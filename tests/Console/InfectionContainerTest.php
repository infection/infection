<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Console;

use Infection\Console\InfectionContainer;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
final class InfectionContainerTest extends TestCase
{
    public function test_it_is_a_usable_container()
    {
        $container = new InfectionContainer();

        $this->assertArrayHasKey('project.dir', $container);
        $this->assertInstanceOf(Container::class, $container);
    }

    public function test_it_can_build_dynamic_dependencies()
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())->method('getOption')->with('coverage')->willReturn('');

        $container = new InfectionContainer();
        $tmpDir = sys_get_temp_dir();
        $container['tmp.dir'] = $tmpDir;

        //Sanity check
        $this->assertArrayNotHasKey('coverage.path', $container);
        $container->buildDynamicDependencies($input);

        $this->assertSame(
            $tmpDir,
            $container['coverage.path']
        );
    }
}
