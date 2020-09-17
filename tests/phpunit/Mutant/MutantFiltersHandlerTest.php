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

namespace Infection\Tests\Mutant;

use Closure;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantFiltersHandler;
use Infection\Plugins\Mutant as PublicMutant;
use Infection\Plugins\MutantFilterPlugin;
use PHPUnit\Framework\TestCase;
use Pipeline\Interfaces\StandardPipeline;
use ReflectionFunction;

/** @internal */
final class MutantFiltersHandlerTest extends TestCase
{
    public function test_it_does_not_adds_callbacks_for_nop_filters(): void
    {
        $filters = [
                $this->createMock(MutantFilterPlugin::class),
                $this->createMock(MutantFilterPlugin::class),
        ];

        $mutants = $this->createMock(StandardPipeline::class);
        $mutants
            ->expects($this->never())
            ->method($this->anything())
        ;

        $handler = new MutantFiltersHandler($filters);

        $handler->applyFilters($mutants);
    }

    public function test_it_adds_callbacks(): void
    {
        $publicMutant = $this->createMock(PublicMutant::class);
        $publicMutant
            ->expects($this->once())
            ->method('getFilePath')
            ->willReturn('/tmp/foo.php')
        ;

        $mutant = $this->createMock(Mutant::class);
        $mutant
            ->expects($this->once())
            ->method('getMutantWrapper')
            ->willReturn($publicMutant)
        ;

        $callable = function (PublicMutant $mutant): bool {
            $this->assertSame('/tmp/foo.php', $mutant->getFilePath());

            return true;
        };

        $filter = $this->createMock(MutantFilterPlugin::class);
        $filter
            ->expects($this->once())
            ->method('getMutantFilter')
            ->willReturn($callable)
        ;

        $mutants = $this->createMock(StandardPipeline::class);
        $mutants
            ->expects($this->once())
            ->method('filter')
            ->with($this->callback(static function (Closure $closure) use ($callable, $mutant) {
                $reflection = new ReflectionFunction($closure);

                return $reflection->getStaticVariables()['filterCallback'] === $callable &&
                    $reflection->getParameters()[0]->getName() === 'mutant' &&
                    $closure($mutant);
            }))
        ;

        $handler = new MutantFiltersHandler([$filter]);

        $handler->applyFilters($mutants);
    }
}
