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

use function class_exists;
use function interface_exists;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use function sprintf;
use function trait_exists;

#[CoversClass(ProjectCodeProvider::class)]
final class ProjectCodeProviderTest extends TestCase
{
    #[DataProviderExternal(ProjectCodeProvider::class, 'sourceClassesProvider')]
    public function test_source_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true)
            || interface_exists($className, true)
            || trait_exists($className, true),
            sprintf(
                'The "%s" class was picked up by the source files finder, but it is not a '
                . 'class, interface or trait. Please check for typos in the class name. If the '
                . ' problematic file is not a class file declaration, add it to the list of '
                . 'excluded files in %s::provideSourceClasses().',
                $className,
                ProjectCodeProvider::class,
            ),
        );
    }

    #[DataProviderExternal(ProjectCodeProvider::class, 'concreteSourceClassesProvider')]
    public function test_concrete_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true),
            sprintf(
                'Expected "%s" to be a class.',
                $className,
            ),
        );
    }

    #[DataProviderExternal(ProjectCodeProvider::class, 'nonTestedConcreteClassesProvider')]
    public function test_non_tested_concrete_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true),
            sprintf(
                'The class "%s" no longer exists. Please remove it from the list of non tested '
                . 'classes in %s::NON_TESTED_CONCRETE_CLASSES.',
                $className,
                ProjectCodeProvider::class,
            ),
        );
    }

    #[DataProviderExternal(ProjectCodeProvider::class, 'sourceClassesToCheckForPublicPropertiesProvider')]
    public function test_source_classes_to_check_for_public_properties_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true) || trait_exists($className, true),
            sprintf(
                'Expected "%s" to be either a class or a trait.',
                $className,
            ),
        );
    }

    #[DataProviderExternal(ProjectCodeProvider::class, 'classesTestProvider')]
    public function test_test_classes_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true)
            || interface_exists($className, true)
            || trait_exists($className, true),
            sprintf(
                'The "%s" class was picked up by the test files finder, but it is not a class,'
                . ' interface or trait. Please check for typos in the class name. If the '
                . ' problematic file is not a class file declaration, add it to the list of '
                . 'excluded files in %s::provideTestClasses().',
                $className,
                ProjectCodeProvider::class,
            ),
        );
    }

    #[DataProviderExternal(ProjectCodeProvider::class, 'nonFinalExtensionClasses')]
    public function test_non_final_extension_classes_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true)
            || interface_exists($className, true)
            || trait_exists($className, true),
            sprintf(
                'The "%s" class was picked up by the test files finder, but it is not a class,'
                . ' interface or trait. Please check for typos in the class name. If the '
                . ' class no longer exists, remove it from %s::NON_FINAL_EXTENSION_CLASSES.',
                $className,
                ProjectCodeProvider::class,
            ),
        );
    }
}
