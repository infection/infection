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

/**
 * @internal
 */
final class InfectionContainerTest extends TestCase
{
    public function test_it_is_a_usable_container()
    {
        $container = new InfectionContainer();
        $this->assertArrayHasKey('project.dir', $container);
    }
}
