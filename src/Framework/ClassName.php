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

namespace Infection\Framework;

use function array_unshift;
use function class_exists;
use function end;
use function explode;
use Infection\CannotBeInstantiated;
use function rtrim;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function substr;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ClassName
{
    use CannotBeInstantiated;

    /**
     * @param class-string $className
     *
     * @return non-empty-string
     */
    public static function getShortClassName(string $className): string
    {
        $parts = explode('\\', $className);

        /** @phpstan-ignore return.type */
        return end($parts);
    }

    /**
     * Gives canonical test class names of the given source class name. It will not autoload
     * any code, hence no class existence check is done.
     *
     * @param class-string $sourceClassName
     *
     * @return class-string[]
     */
    public static function getCanonicalTestClassNames(string $sourceClassName): array
    {
        Assert::true(
            str_starts_with($sourceClassName, 'Infection\\'),
            sprintf(
                'Expected source fully-qualified class name to be a source file from Infection. Got "%s".',
                $sourceClassName,
            ),
        );

        $classNameWithCorrectedNamespace = str_starts_with($sourceClassName, 'Infection\\Tests\\')
            ? $sourceClassName
            : 'Infection\\Tests\\' . substr($sourceClassName, 10);

        $shortClassNameName = self::getShortClassName($sourceClassName);

        return [
            $classNameWithCorrectedNamespace . 'Test',
            sprintf(
                '%s%2$s\\%2$sTest',
                rtrim($classNameWithCorrectedNamespace, $shortClassNameName),
                $shortClassNameName,
            ),
        ];
    }

    /**
     * Looks up at the canonical test class names and check for their existences to return
     * the one that exists if there is any.
     *
     * Beware that this triggers a code autoload.
     *
     * @param class-string $sourceClassName
     *
     * @return class-string|null
     */
    public static function getCanonicalTestClassName(string $sourceClassName): ?string
    {
        foreach (self::getCanonicalTestClassNames($sourceClassName) as $testClassName) {
            if (class_exists($testClassName)) {
                return $testClassName;
            }
        }

        return null;
    }

    /**
     * Gives canonical source class names of the given test class name. It will not autoload
     * any code, hence no class existence check is done.
     *
     * @param class-string $testClassName
     *
     * @return class-string[]
     */
    public static function getCanonicalSourceClassNames(string $testClassName): array
    {
        Assert::true(
            str_starts_with($testClassName, 'Infection\\'),
            sprintf(
                'Expected test fully-qualified class name to be a test file from Infection. Got "%s".',
                $testClassName,
            ),
        );
        Assert::true(
            str_ends_with($testClassName, 'Test'),
            sprintf(
                'Expected test fully-qualified class name to follow the PHPUnit test naming convention, i.e. to have the suffix "Test". Got "%s".',
                $testClassName,
            ),
        );

        $classNameWithCorrectedNamespaceAndWithoutSuffix = str_starts_with($testClassName, 'Infection\\Tests\\')
            ? 'Infection\\' . substr($testClassName, 16, -4)
            : substr($testClassName, 0, -4);

        $candidates = [
            $classNameWithCorrectedNamespaceAndWithoutSuffix,
        ];

        $shortClassNameName = self::getShortClassName($classNameWithCorrectedNamespaceAndWithoutSuffix);

        if (str_ends_with($classNameWithCorrectedNamespaceAndWithoutSuffix, $shortClassNameName . '\\' . $shortClassNameName)) {
            array_unshift(
                $candidates,
                substr($classNameWithCorrectedNamespaceAndWithoutSuffix, 0, -(strlen($shortClassNameName) + 1)),
            );
        }

        return $candidates;
    }

    /**
     * Looks up at the canonical source class names and check for their existences to return
     * the one that exists if there is any.
     *
     * Beware that this triggers a code autoload.
     *
     * @return class-string|null
     */
    public static function getCanonicalSourceClassName(string $testClassName): ?string
    {
        foreach (self::getCanonicalSourceClassNames($testClassName) as $sourceClassName) {
            if (class_exists($sourceClassName)) {
                return $sourceClassName;
            }
        }

        return null;
    }
}
