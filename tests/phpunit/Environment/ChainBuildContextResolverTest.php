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

namespace Infection\Tests\Environment;

use function array_map;
use Infection\Environment\BuildContext;
use Infection\Environment\BuildContextResolver;
use Infection\Environment\ChainBuildContextResolver;
use Infection\Environment\CouldNotResolveBuildContext;
use PHPUnit\Framework\TestCase;
use function range;

final class ChainBuildContextResolverTest extends TestCase
{
    public function test_resolve_throws_could_not_resolve_build_context_when_chain_is_empty(): void
    {
        $environment = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $buildContextResolver = new ChainBuildContextResolver();

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('Build context could not be resolved.');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_throws_could_not_resolve_build_context_when_none_of_the_build_context_resolvers_could_resolve_the_build_context(): void
    {
        $environment = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $incapableBuildContextResolvers = array_map(function () use ($environment): BuildContextResolver {
            $buildContextResolver = $this->createMock(BuildContextResolver::class);

            $buildContextResolver
                ->expects($this->once())
                ->method('resolve')
                ->with($this->identicalTo($environment))
                ->willThrowException(new CouldNotResolveBuildContext());

            return $buildContextResolver;
        }, range(0, 4));

        $buildContextResolver = new ChainBuildContextResolver(...$incapableBuildContextResolvers);

        $this->expectException(CouldNotResolveBuildContext::class);
        $this->expectExceptionMessage('Build context could not be resolved.');

        $buildContextResolver->resolve($environment);
    }

    public function test_resolve_returns_build_context_of_first_build_context_resolver_that_could_resolve_the_build_context(): void
    {
        $environment = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $buildContext = new BuildContext(
            'example/example',
            'feature/this'
        );

        $incapableBuildContextResolvers = array_map(function () use ($environment): BuildContextResolver {
            $buildContextResolver = $this->createMock(BuildContextResolver::class);

            $buildContextResolver
                ->expects($this->once())
                ->method('resolve')
                ->with($this->identicalTo($environment))
                ->willThrowException(new CouldNotResolveBuildContext());

            return $buildContextResolver;
        }, range(0, 2));

        $capableBuildContextResolver = $this->createMock(BuildContextResolver::class);

        $capableBuildContextResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->identicalTo($environment))
            ->willReturn($buildContext);

        $remainingBuildContextResolvers = array_map(function (): BuildContextResolver {
            return $this->createMock(BuildContextResolver::class);
        }, range(0, 2));

        $buildContextResolvers = array_merge(
            $incapableBuildContextResolvers,
            [
                $capableBuildContextResolver,
            ],
            $remainingBuildContextResolvers
        );

        $buildContextResolver = new ChainBuildContextResolver(...$buildContextResolvers);

        $this->assertSame($buildContext, $buildContextResolver->resolve($environment));
    }
}
