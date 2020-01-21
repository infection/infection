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

namespace Infection\Tests\Mutagen\Mutator;

use function array_keys;
use function array_unique;
use function array_values;
use function in_array;
use Infection\Mutagen\Mutator\MutatorCategory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\sprintf;

final class MutatorCategoryTest extends TestCase
{
    private const ALL_CONSTANT_KEY = 'ALL';

    public function test_it_cannot_be_instantiated(): void
    {
        $classReflection = new ReflectionClass(MutatorCategory::class);

        $this->assertFalse(
            $classReflection->isInstantiable(),
            sprintf('Did not expect "%s" to be instantiable', MutatorCategory::class)
        );
    }

    public function test_it_lists_all_its_exposed_constants(): void
    {
        $enumClass = MutatorCategory::class;

        $categoryReflection = new ReflectionClass($enumClass);

        if (!$categoryReflection->hasConstant(self::ALL_CONSTANT_KEY)) {
            return;
        }

        $this->assertAllIsPublic($enumClass, $categoryReflection);

        $constants = $categoryReflection->getConstants();

        $this->assertAllDoesNotHaveDuplicatedValues($enumClass, $constants);

        $this->assertAllListTheExposedConstants($enumClass, $constants);

        $this->assertExposedConstantsArePublic($enumClass, $categoryReflection, $constants);
    }

    private function assertAllIsPublic(string $enumClass, ReflectionClass $classReflection): void
    {
        $allConstantReflection = $classReflection->getReflectionConstant(self::ALL_CONSTANT_KEY);

        $this->assertTrue(
            $allConstantReflection->isPublic(),
            sprintf(
                'Expected enum "%s#%s" constant to be public',
                $enumClass,
                self::ALL_CONSTANT_KEY
            )
        );
    }

    private function assertAllDoesNotHaveDuplicatedValues(string $enumClass, array $constants): void
    {
        $all = $constants[self::ALL_CONSTANT_KEY];
        unset($constants[self::ALL_CONSTANT_KEY]);

        $this->assertSame(
            array_unique($all),
            $all,
            sprintf(
                'Did not expect the constant "%s#%s" to have duplicated values',
                $enumClass,
                self::ALL_CONSTANT_KEY
            )
        );
    }

    private function assertAllListTheExposedConstants(string $enumClass, array $constants): void
    {
        $all = $constants[self::ALL_CONSTANT_KEY];
        unset($constants[self::ALL_CONSTANT_KEY]);

        $this->assertSame(
            array_values($all),
            array_values($constants),
            sprintf(
                'Expected the constant "%s#%s" to list all of the enums constants',
                $enumClass,
                self::ALL_CONSTANT_KEY
            )
        );
    }

    private function assertExposedConstantsArePublic(
        string $enumClass,
        ReflectionClass $classReflection,
        array $constants
    ): void {
        foreach (array_keys($constants) as $constantName) {
            if ($constantName !== self::ALL_CONSTANT_KEY
                && !in_array($constants[$constantName], $constants[self::ALL_CONSTANT_KEY], true)
            ) {
                continue;
            }

            $constantReflection = $classReflection->getReflectionConstant($constantName);

            $this->assertTrue(
                $constantReflection->isPublic(),
                sprintf(
                    'Expected the constant "%s#%s" exposed by the enum to be public',
                    $enumClass,
                    $constantName
                )
            );
        }
    }
}
