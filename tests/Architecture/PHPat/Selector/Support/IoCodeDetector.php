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

namespace Infection\Tests\Architecture\PHPat\Selector\Support;

use function count;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\Analyser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\TestCase;

final class IoCodeDetector
{
    /**
     * @var array<class-string, bool>
     */
    private array $classUsesIoCache = [];

    public function __construct(
        private readonly Analyser $analyser,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function isUsingIo(ClassReflection $testCaseReflection): bool
    {
        if ($this->testCaseUsesIo($testCaseReflection)) {
            return true;
        }

        $coveredClassNames = PHPUnitTestClassAnalysis::getCoveredClassNames(
            $testCaseReflection,
            $this->reflectionProvider,
        );

        if (count($coveredClassNames) === 0) {
            return true;
        }

        foreach ($coveredClassNames as $coveredClassName) {
            if ($this->testedClassUsesIo($coveredClassName)) {
                return true;
            }
        }

        return false;
    }

    public function hasCoveredClass(ClassReflection $testCaseReflection): bool
    {
        $coveredClassNames = PHPUnitTestClassAnalysis::getCoveredClassNames(
            $testCaseReflection,
            $this->reflectionProvider,
        );

        return count($coveredClassNames) > 0;
    }

    /**
     * Check the test case code.
     */
    private function testCaseUsesIo(ClassReflection $testCaseReflection): bool
    {
        if ($this->doesClassUseIo($testCaseReflection)) {
            return true;
        }

        $parentTestCaseClassReflection = $testCaseReflection->getParentClass();

        while (
            $parentTestCaseClassReflection !== null
            && $parentTestCaseClassReflection->getName() !== TestCase::class
        ) {
            if ($this->doesClassUseIo($parentTestCaseClassReflection)) {
                return true;
            }

            $parentTestCaseClassReflection = $parentTestCaseClassReflection->getParentClass();
        }

        return false;
    }

    /**
     * Check the source class code.
     *
     * @param class-string $sourceClassName
     */
    private function testedClassUsesIo(string $sourceClassName): bool
    {
        $classReflection = $this->reflectionProvider->getClass($sourceClassName);

        do {
            if ($this->doesClassUseIo($classReflection)) {
                return true;
            }

            $classReflection = $classReflection->getParentClass();
        } while ($classReflection !== null);

        return false;
    }

    private function doesClassUseIo(ClassReflection $classReflection): bool
    {
        $className = $classReflection->getName();

        if (isset($this->classUsesIoCache[$className])) {
            return $this->classUsesIoCache[$className];
        }

        $fileName = $classReflection->getFileName();

        return $this->classUsesIoCache[$className] = $fileName !== null
            && $this->analyser->analyse($classReflection, analyseNonConcreteClasses: true)->usesIo;
    }
}
