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

namespace Infection\Tests\AutoReview\ProjectCode;

use function array_filter;
use function array_map;
use Infection\StreamWrapper\IncludeInterceptor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use function Safe\preg_replace;
use function Safe\sprintf;

/**
 * @coversNothing
 *
 * This class is responsible for testing that our code base adheres to certain rules,
 * e.g. 'All classes that aren't intended to be used by users should be marked internal'
 *
 * The goal is to reduce pr reviews about style issues that can't be automatically fixed.
 * All test failures should be clear in meaning, to help new contributors.
 */
final class ProjectCodeTest extends TestCase
{
    /**
     * @requires OSFAMILY Windows Cannot check if the file is executable on Windows
     */
    public function test_infection_bin_is_executable(): void
    {
        $infectionFile = __DIR__ . '/../../../../bin/infection';

        $this->assertFileExists($infectionFile);
        $this->assertTrue(is_executable($infectionFile));
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider::concreteSourceClassesProvider
     */
    public function test_all_concrete_classes_have_tests(string $className): void
    {
        $testClassName = preg_replace('/Infection/', 'Infection\\Tests', $className, 1) . 'Test';

        if (false === \in_array($className, ProjectCodeProvider::NON_TESTED_CONCRETE_CLASSES, true)) {
            $this->assertTrue(
                class_exists($testClassName, true),
                sprintf(
                    'Could not find the test "%s" for the class "%s". Please either add it'
                    . ' or add it to %s::NON_TESTED_CONCRETE_CLASSES',
                    $testClassName,
                    $className,
                    ProjectCodeProvider::class
                )
            );

            return;
        }

        $this->assertFalse(
            class_exists($testClassName, true),
            sprintf(
                'The class "%s" has a test "%s". Please remove it from the list of non '
                . 'tested concrete classes in %s::NON_TESTED_CONCRETE_CLASSES',
                $className,
                $testClassName,
                ProjectCodeProvider::class
            )
        );

        $this->markTestSkipped(sprintf(
            'No test found for "%s". You can improve this by adding the test "%s".',
            $className,
            $testClassName
        ));
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider::sourceClassesProvider
     */
    public function test_non_extension_points_are_internal(string $className): void
    {
        $reflectionClass = new ReflectionClass($className);

        $docBlock = DocBlockParser::parse((string) $reflectionClass->getDocComment());

        if (\in_array($className, ProjectCodeProvider::EXTENSION_POINTS, true)) {
            if ($docBlock === '') {
                $this->markTestSkipped(
                    sprintf(
                        'The "%s" class is an extension point, but does not have a PHP '
                        . 'doc-block or an empty one. Consider adding one to improve usability.',
                        $className
                    )
                );
            }

            $this->assertStringNotContainsString(
                '@internal',
                $docBlock,
                sprintf(
                    'The "%s" class is marked as an extension point in %s::EXTENSION_POINTS'
                    . '; It should either not be tagged as "@internal" or not be listed there.',
                    $className,
                    ProjectCodeProvider::class
                )
            );

            return;
        }

        $this->assertStringContainsString(
            '@internal',
            $docBlock,
            sprintf(
                'The "%s" class is not an extension point: it should be marked as internal'
                . ' or listed as an extension point in %s::EXTENSION_POINTS.',
                $className,
                ProjectCodeProvider::class
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider::sourceClassesProvider
     */
    public function test_non_extension_points_are_traits_interfaces_abstracts_or_finals(string $className): void
    {
        $reflectionClass = new ReflectionClass($className);

        if (\in_array($className, ProjectCodeProvider::NON_FINAL_EXTENSION_CLASSES, true)) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->assertTrue(
            $reflectionClass->isTrait()
            || $reflectionClass->isInterface()
            || $reflectionClass->isAbstract()
            || $reflectionClass->isFinal(),
            sprintf(
                'Expected the class "%s" to be a trait, an interface, an abstract or final '
                . 'class. Either fix it or if it is an extension point, add it to '
                . '%s::NON_FINAL_EXTENSION_CLASSES.',
                $className,
                ProjectCodeProvider::class
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider::sourceClassesToCheckForPublicPropertiesProvider
     */
    public function test_source_classes_do_not_expose_public_properties(string $className): void
    {
        $reflectionClass = new ReflectionClass($className);

        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        if ($className === IncludeInterceptor::class) {
            // The IncludeInterceptor needs 1 public property: $context
            // @see https://secure.php.net/manual/en/class.streamwrapper.php
            $this->assertCount(
                1,
                $properties,
                sprintf(
                    'The "%s" class must have exactly 1 public property as it is a streamwrapper. ' .
                    'If this has changed due to recent PHP developments, consider updating this test.',
                    $className
                )
            );

            $this->assertSame(
                'context',
                $properties[0]->getName(),
                sprintf(
                    'The "%s" class must have exactly 1 public property named "context". ' .
                    'If this has changed due to recent PHP developments, consider updating this test.',
                    $className
                )
            );

            return;
        }

        // We should consider only properties belonging to our classes, but not to foreign classes
        // we're extending from, e.g. we can't change Symfony\Component\Process\Process to not have
        // a public property it has.
        $propertyNames = array_map(
            static function (ReflectionProperty $reflectionProperty): string {
                return sprintf(
                    '%s#%s',
                    $reflectionProperty->getDeclaringClass()->getName(),
                    $reflectionProperty->getName()
                );
            },
            array_filter(
                $properties,
                static function (ReflectionProperty $property) use ($className): bool {
                    return $property->class === $className;
                }
            )
        );

        $this->assertSame(
            [],
            $propertyNames,
            sprintf(
                'The class "%s" should not have any public properties declared. If it has '
                . 'properties that needs to be accessed, getters should be used instead.',
                $className
            )
        );
    }

    /**
     * @dataProvider \Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider::classesTestProvider
     */
    public function test_all_test_classes_are_trait_abstract_or_final(string $className): void
    {
        $reflectionClass = new ReflectionClass($className);

        $this->assertTrue(
            $reflectionClass->isTrait()
            || $reflectionClass->isAbstract()
            || $reflectionClass->isFinal(),
            sprintf(
                'The test class "%s" should be a trait, an abstract or final class.',
                $className
            )
        );
    }
}
