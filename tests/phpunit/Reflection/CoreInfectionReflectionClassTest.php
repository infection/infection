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

namespace Infection\Tests\Reflection;

use Generator;
use Infection\Reflection\CoreInfectionReflectionClass;
use Infection\Reflection\Visibility;
use Infection\Visitor\CloneVisitor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CoreInfectionReflectionClassTest extends TestCase
{
    public function test_it_wraps_the_name(): void
    {
        $reflection = new CoreInfectionReflectionClass(new ReflectionClass($this));
        $this->assertSame($reflection->getName(), get_class($this));
    }

    /**
     * @dataProvider provideParentMethodCases
     */
    public function test_it_knows_if_a_function_is_inherited(CoreInfectionReflectionClass $reflection, string $method, Visibility $visibility, bool $hasParent): void
    {
        $this->assertSame($hasParent, $reflection->hasParentOfVisibility($method, $visibility));
    }

    public function provideParentMethodCases(): Generator
    {
        yield [
            new CoreInfectionReflectionClass(new ReflectionClass(new CloneVisitor())),
            'enterNode',
            Visibility::asPublic(),
            true,
        ];

        yield [
            new CoreInfectionReflectionClass(new ReflectionClass(new CloneVisitor())),
            'enterNode',
            Visibility::asProtected(),
            false,
        ];

        yield [
            new CoreInfectionReflectionClass(new ReflectionClass($this)),
            'foo',
            Visibility::asProtected(),
            false,
        ];

        yield [
            new CoreInfectionReflectionClass(new ReflectionClass($this)),
            'foo',
            Visibility::asPublic(),
            false,
        ];

        yield [
            new CoreInfectionReflectionClass(new ReflectionClass(ProtChild::class)),
            'foo',
            Visibility::asPublic(),
            false,
        ];

        yield [
            new CoreInfectionReflectionClass(new ReflectionClass(ProtChild::class)),
            'foo',
            Visibility::asProtected(),
            true,
        ];
    }
}

class ProtParent
{
    protected function foo(): void
    {
    }
}

class ProtChild extends ProtParent
{
    protected function foo(): void
    {
    }
}
