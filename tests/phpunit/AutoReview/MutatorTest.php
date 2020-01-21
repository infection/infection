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

namespace Infection\Tests\AutoReview;

use function array_column;
use function array_diff;
use function array_filter;
use function array_map;
use function count;
use Generator;
use function implode;
use Infection\Mutagen\Mutator\Mutator;
use function Infection\Tests\generator_to_phpunit_data_provider;
use Infection\Tests\Mutagen\Mutator\ProfileListProvider;
use function iterator_to_array;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use function Safe\sprintf;
use function sort;
use const SORT_STRING;

/**
 * @coversNothing
 *
 * This class is responsible for testing that all Mutator classes adhere to certain rules e.g.
 * 'Mutators shouldn't declare any public methods'.
 *
 * The goal is to reduce PR reviews about style issues that can't be automatically fixed. All test
 * failures should have a clear explanation to help contributors unfamiliar with the codebase.
 */
final class MutatorTest extends TestCase
{
    private const KNOWN_MUTATOR_PUBLIC_METHODS = [
        'getDefinition',
        'getName',
        'mutate',
        'shouldMutate',
    ];

    /**
     * @dataProvider mutatorClassesProvider
     */
    public function test_mutators_do_not_declare_public_methods(string $className): void
    {
        $publicMethods = $this->getPublicMethods(new ReflectionClass($className));

        $this->assertCount(
            count(self::KNOWN_MUTATOR_PUBLIC_METHODS),
            $publicMethods,
            sprintf(
                'The mutator class "%s" has the following non-allowed public method(s) '
                . 'declared: "%s". Either reconsider if it is necessary for it to be public and make'
                . ' it protected/private instead or add it to "%s::KNOWN_MUTATOR_PUBLIC_METHODS".',
                $className,
                implode(
                    ', ',
                    array_diff($publicMethods, self::KNOWN_MUTATOR_PUBLIC_METHODS)
                ),
                self::class
            )
        );
    }

    /**
     * @dataProvider concreteMutatorClassesProvider
     */
    public function test_mutators_have_a_definition(string $className): void
    {
        /** @var Mutator $mutator */
        $mutator = (new ReflectionClass($className))->newInstanceWithoutConstructor();

        $definition = $mutator::getDefinition();

        if ($definition !== null) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->addWarning(sprintf(
            'The mutant "%s" does not have a definition.',
            $className
        ));
    }

    public function mutatorClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(array_column(
            iterator_to_array(ProfileListProvider::mutatorNameAndClassProvider(), true),
            1
        ));
    }

    public function concreteMutatorClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(ConcreteClassReflector::filterByConcreteClasses(
            array_column(
                iterator_to_array(ProfileListProvider::mutatorNameAndClassProvider(), true),
                1
            )
        ));
    }

    /**
     * @return string[]
     */
    private function getPublicMethods(ReflectionClass $reflectionClass): array
    {
        $publicMethods = array_map(
            static function (ReflectionMethod $reflectionMethod): string {
                return $reflectionMethod->getName();
            },
            array_filter(
                $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC),
                static function (ReflectionMethod $reflectionMethod): bool {
                    return !$reflectionMethod->isConstructor();
                }
            )
        );

        sort($publicMethods, SORT_STRING);

        return $publicMethods;
    }
}
