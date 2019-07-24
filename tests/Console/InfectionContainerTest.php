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

namespace Infection\Tests\Console;

use Infection\Console\InfectionContainer;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
final class InfectionContainerTest extends TestCase
{
    public function test_it_can_be_instantiated_without_any_services(): void
    {
        $container = new InfectionContainer();

        $this->assertSame([], $container->keys());
    }

    public function test_it_can_be_instantiated_with_services(): void
    {
        $syntheticService = (object) ['synthetic' => true];
        $regularService = static function (): stdClass {
            return (object) [
                'regular' => true,
            ];
        };

        $container = new InfectionContainer([
            'synthetic service' => $syntheticService,
            'regular service' => $regularService,
        ]);

        $this->assertSame(
            [
                'synthetic service',
                'regular service',
            ],
            $container->keys()
        );

        $this->assertSame($syntheticService, $container['synthetic service']);
        $this->assertEquals(
            (object) [
                'regular' => true,
            ],
            $container['regular service']
        );
    }

    public function test_it_can_be_instantiated_with_the_project_services(): void
    {
        $container = InfectionContainer::create();

        $this->assertNotSame([], $container->keys());
    }

    public function test_it_can_build_dynamic_dependencies(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())->method('hasOption')->with('coverage')->willReturn(false);

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

    public function test_it_throws_on_invalid_type(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input
            ->method('hasOption')
            ->with('coverage')
            ->willReturn(false)
        ;
        $input
            ->method('getOption')
            ->with('initial-tests-php-options')
            ->willReturn([])
        ;

        $container = new InfectionContainer();
        $container->buildDynamicDependencies($input);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected initial-tests-php-options to be string, array given');
        $this->assertNotNull($container['coverage.checker']);
    }
}
