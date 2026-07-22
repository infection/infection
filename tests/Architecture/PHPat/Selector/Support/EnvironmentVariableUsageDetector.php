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

use function array_merge;
use function array_unique;
use function array_values;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\Analyser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\TestCase;

final readonly class EnvironmentVariableUsageDetector
{
    public function __construct(
        private Analyser $analyser,
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getEnvironmentVariables(ClassReflection $testCaseReflection): array
    {
        $environmentVariables = $this->getTestCaseEnvironmentVariables($testCaseReflection);

        $coveredSymbols = PHPUnitTestClassAnalysis::getCoveredSymbols(
            $testCaseReflection,
            $this->reflectionProvider
        );

        foreach ($coveredSymbols as $coveredSymbol) {
            $environmentVariables = [
                ...$environmentVariables,
                ...$this->getSourceClassEnvironmentVariables($coveredSymbol),
            ];
        }

        return array_values(array_unique($environmentVariables));
    }

    /**
     * @return list<string>
     */
    private function getTestCaseEnvironmentVariables(ClassReflection $testCaseReflection): array
    {
        $environmentVariablesList = [];

        do {
            $environmentVariablesList[] = $this->getClassEnvironmentVariables($testCaseReflection);

            $testCaseReflection = $testCaseReflection->getParentClass();
        } while ($testCaseReflection !== null && $testCaseReflection->getName() !== TestCase::class);

        return array_merge(...$environmentVariablesList);
    }

    /**
     * @param class-string $className
     *
     * @return list<string>
     */
    private function getSourceClassEnvironmentVariables(string $className): array
    {
        $environmentVariablesList = [];
        $classReflection = $this->reflectionProvider->getClass($className);

        do {
            $environmentVariablesList[] = $this->getClassEnvironmentVariables($classReflection);

            $classReflection = $classReflection->getParentClass();
        } while ($classReflection !== null);

        return array_merge(...$environmentVariablesList);
    }

    /**
     * @return list<string>
     */
    private function getClassEnvironmentVariables(ClassReflection $classReflection): array
    {
        if ($classReflection->getFileName() === null) {
            return [];
        }

        return $this->analyser
            ->analyse($classReflection, analyseNonConcreteClasses: true)
            ->environmentVariables;
    }
}
