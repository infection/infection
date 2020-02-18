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

use function array_diff;
use function array_filter;
use function array_map;
use function count;
use function implode;
use function in_array;
use Infection\Mutator\ConfigurableMutator;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorConfig;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use function Safe\class_implements;
use function Safe\sort;
use function Safe\sprintf;
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
        'canMutate',
    ];

    /**
     * @dataProvider \Infection\Tests\AutoReview\Mutator\MutatorProvider::mutatorClassesProvider
     */
    public function test_mutators_do_not_declare_public_methods(string $className): void
    {
        $publicMethods = $this->getPublicMethods(new ReflectionClass($className));

        $knownMutatorPublicMethodNames = self::KNOWN_MUTATOR_PUBLIC_METHODS;

        if (in_array(ConfigurableMutator::class, class_implements($className), true)) {
            $knownMutatorPublicMethodNames[] = 'getConfigClassName';
        }

        $this->assertCount(
            count($knownMutatorPublicMethodNames),
            $publicMethods,
            sprintf(
                <<<'TXT'
The mutator class "%s" has the following non-allowed public method(s) declared: "%s". Either
reconsider if it is necessary for it to be public and make it protected/private instead or add it
to "%s::KNOWN_MUTATOR_PUBLIC_METHODS".
TXT
                ,
                $className,
                implode(
                    ', ',
                    array_diff($publicMethods, $knownMutatorPublicMethodNames)
                ),
                self::class
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\Mutator\MutatorProvider::concreteMutatorClassesProvider
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
            'The mutator "%s" does not have a definition.',
            $className
        ));
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\Mutator\MutatorProvider::concreteMutatorClassesProvider
     */
    public function test_configurable_mutators_declare_a_mutator_config(string $className): void
    {
        $mutatorReflection = new ReflectionClass($className);

        $isConfigurable = in_array(
            ConfigurableMutator::class,
            class_implements($className),
            true
        );
        $configClassName = $isConfigurable ? $className::getConfigClassName() : null;

        $constructorReflection = $mutatorReflection->getConstructor();

        if ($constructorReflection === null) {
            $this->assertFalse(
                $isConfigurable,
                sprintf(
                    <<<'TXT'
The mutator "%s" is a configurable mutator but its constructor does not require a configuration.
The constructor should either require a "%s" parameter or the mutator should not
implement "%s".
TXT
                    ,
                    $className,
                    $configClassName ?: MutatorConfig::class,
                    ConfigurableMutator::class
                )
            );
        } else {
            $constructorParameters = $constructorReflection->getParameters();

            $assertionErrorMessage = sprintf(
                <<<'TXT'
Expected the mutator "%s" to have the constructor signature "__construct(%s $config)".
TXT
                ,
                $className,
                $configClassName
            );

            $this->assertCount(
                1,
                $constructorParameters,
                $assertionErrorMessage . ' The constructor parameter count does not match.'
            );

            $configParameterType = $constructorParameters[0]->getType();

            $this->assertNotNull(
                $configParameterType,
                $assertionErrorMessage . ' The constructor parameter type does not match.'
            );
            $this->assertInstanceOf(ReflectionNamedType::class, $configParameterType);

            $this->assertSame(
                $configClassName,
                $configParameterType->getName(),
                $assertionErrorMessage . ' The constructor parameter type does not match.'
            );
        }
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\Mutator\MutatorProvider::configurableMutatorClassesProvider
     */
    public function test_only_configurable_mutators_have_a_config(string $className): void
    {
        $configClassName = $className::getConfigClassName();

        $this->assertContains(
            MutatorConfig::class,
            class_implements($configClassName),
            sprintf(
                <<<'TXT'
Expected the mutator configuration class "%s" for the mutator "%s" to be a "%s".
TXT
                ,
                $configClassName,
                $className,
                MutatorConfig::class
            )
        );
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
