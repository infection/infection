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

namespace Infection\Tests\Architecture\PHPat\Selector;

use Infection\Framework\ClassName;
use PHPat\Selector\SelectorInterface;
use PHPStan\Reflection\ClassReflection;
use function str_ends_with;
use Symfony\Component\Filesystem\Path;

final class TestingUtilityConcreteClassWithoutCanonicalTest implements SelectorInterface
{
    private const string PROJECT_ROOT = __DIR__ . '/../../../../';

    public function getName(): string
    {
        return 'testing utility concrete class without canonical test';
    }

    public function matches(ClassReflection $classReflection): bool
    {
        if (!self::isConcreteClass($classReflection)
            || self::isTestClass($classReflection)
            || !self::isTestingUtilityCode($classReflection)
        ) {
            return false;
        }

        $className = $classReflection->getName();

        return ClassName::getCanonicalTestClassName($className) === null;
    }

    private static function isConcreteClass(ClassReflection $classReflection): bool
    {
        return !$classReflection->isAbstract()
            && !$classReflection->isInterface()
            && !$classReflection->isTrait();
    }

    private static function isTestingUtilityCode(ClassReflection $classReflection): bool
    {
        $fileName = $classReflection->getFileName();

        return $fileName !== null
            && Path::isBasePath(
                'tests/phpunit/TestingUtility',
                Path::makeRelative($fileName, self::PROJECT_ROOT),
            );
    }

    private static function isTestClass(ClassReflection $classReflection): bool
    {
        return str_ends_with($classReflection->getName(), 'Test');
    }
}
