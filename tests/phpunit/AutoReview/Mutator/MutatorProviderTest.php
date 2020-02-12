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

namespace Infection\Tests\AutoReview\Mutator;

use function in_array;
use Infection\Mutator\ConfigurableMutator;
use Infection\Mutator\Mutator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\class_implements;
use function Safe\sprintf;

/**
 * @covers \Infection\Tests\AutoReview\Mutator\MutatorProvider
 */
final class MutatorProviderTest extends TestCase
{
    /**
     * @dataProvider \Infection\Tests\AutoReview\Mutator\MutatorProvider::mutatorClassesProvider
     */
    public function test_mutator_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true)
            && in_array(Mutator::class, class_implements($className), true),
            sprintf(
                'The "%s" class was picked up by the mutator class finder, but it is not a ' .
                '"%s".',
                $className,
                Mutator::class
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\Mutator\MutatorProvider::concreteMutatorClassesProvider
     */
    public function test_concrete_mutator_class_provider_is_valid(string $className): void
    {
        $reflectionClass = new ReflectionClass($className);

        $this->assertFalse(
            $reflectionClass->isAbstract(),
            sprintf(
                'The "%s" mutator class was picked up by the concrete mutator class finder,'
                . ' but it is not a concrete class',
                $className
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\Mutator\MutatorProvider::configurableMutatorClassesProvider
     */
    public function test_configurable_mutator_class_provider_is_valid(string $className): void
    {
        $reflectionClass = new ReflectionClass($className);

        $this->assertFalse(
            $reflectionClass->isAbstract(),
            sprintf(
                'The "%s" mutator class was picked up by the configurable mutator class finder,'
                . ' but it is not a concrete class',
                $className
            )
        );
        $this->assertTrue(
            $reflectionClass->implementsInterface(ConfigurableMutator::class),
            sprintf(
                'The "%s" mutator class was picked up by the configurable mutator class finder,'
                . ' but it does not implement the "%s" interface',
                $className,
                ConfigurableMutator::class
            )
        );
    }
}
