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

use function array_column;
use Generator;
use function in_array;
use Infection\Mutator\ConfigurableMutator;
use Infection\Tests\AutoReview\ConcreteClassReflector;
use function Infection\Tests\generator_to_phpunit_data_provider;
use Infection\Tests\Mutator\ProfileListProvider;
use function iterator_to_array;
use function Safe\class_implements;

/**
 * @coversNothing
 *
 * This class is responsible for testing that all Mutator classes adhere to certain rules e.g.
 * 'Mutators shouldn't declare any public methods'.
 *
 * The goal is to reduce PR reviews about style issues that can't be automatically fixed. All test
 * failures should have a clear explanation to help contributors unfamiliar with the codebase.
 */
final class MutatorProvider
{
    /**
     * @var string[]|null
     */
    private static $mutatorClasses;

    /**
     * @var string[]|null
     */
    private static $concreteMutatorClasses;

    /**
     * @var string[]|null
     */
    private static $configurableMutatorClasses;

    private function __construct()
    {
    }

    public static function provideMutatorClassesProvider(): Generator
    {
        if (self::$mutatorClasses === null) {
            self::$mutatorClasses = array_column(
                iterator_to_array(ProfileListProvider::mutatorNameAndClassProvider(), true),
                1
            );
        }

        yield from self::$mutatorClasses;
    }

    public static function provideConcreteMutatorClassesProvider(): Generator
    {
        if (self::$concreteMutatorClasses === null) {
            self::$concreteMutatorClasses = ConcreteClassReflector::filterByConcreteClasses(
                iterator_to_array(self::provideMutatorClassesProvider(), false)
            );
        }

        yield from self::$concreteMutatorClasses;
    }

    public static function provideConfigurableMutatorClassesProvider(): Generator
    {
        if (self::$configurableMutatorClasses === null) {
            self::$configurableMutatorClasses = [];

            foreach (self::provideConcreteMutatorClassesProvider() as $mutatorClassName) {
                if (in_array(ConfigurableMutator::class, class_implements($mutatorClassName), true)) {
                    self::$configurableMutatorClasses[] = $mutatorClassName;
                }
            }
        }

        yield from self::$configurableMutatorClasses;
    }

    public static function mutatorClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(self::provideMutatorClassesProvider());
    }

    public static function concreteMutatorClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(self::provideConcreteMutatorClassesProvider());
    }

    public static function configurableMutatorClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(self::provideConfigurableMutatorClassesProvider());
    }
}
