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

use function array_filter;
use function array_values;
use function count;
use Infection\Tests\Architecture\PHPat\Selector\Support\ClassReflectionPredicates;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitecture;
use PHPat\Selector\SelectorInterface;
use PHPStan\Reflection\ClassReflection;
use ReflectionMethod;
use ReflectionNamedType;
use function strtolower;

final readonly class SingleEventSubscriberWithoutExpectedMethod implements SelectorInterface
{
    public function __construct(
        private EventArchitecture $eventArchitecture,
    ) {
    }

    public function getName(): string
    {
        return 'single-event subscriber without expected method';
    }

    public function matches(ClassReflection $classReflection): bool
    {
        if (!$this->eventArchitecture->isSingleEventSubscriber($classReflection)) {
            return false;
        }

        $methods = $this->getDeclaredPublicMethods($classReflection);

        if (count($methods) !== 1) {
            return true;
        }

        $method = $methods[0];

        return !$this->hasExpectedMethodSignature($classReflection, $method);
    }

    private function hasExpectedMethodSignature(
        ClassReflection $classReflection,
        ReflectionMethod $method,
    ): bool {
        return $this->isExpectedMethodName($classReflection, $method)
            && $this->isExpectedParameterSignature($classReflection, $method)
            && !$method->isStatic()
            && self::isVoidReturnType($method);
    }

    /**
     * @return list<ReflectionMethod>
     */
    private function getDeclaredPublicMethods(ClassReflection $classReflection): array
    {
        $isDeclaredByTheSubscriber = static fn (ReflectionMethod $method): bool => !ClassReflectionPredicates::isInheritedMethod($method, $classReflection);

        return array_values(
            array_filter(
                $classReflection->getNativeReflection()->getMethods(ReflectionMethod::IS_PUBLIC),
                $isDeclaredByTheSubscriber,
            ),
        );
    }

    private static function isVoidReturnType(ReflectionMethod $method): bool
    {
        $returnType = $method->getReturnType();

        return $returnType instanceof ReflectionNamedType
            && $returnType->isBuiltin()
            && strtolower($returnType->getName()) === 'void';
    }

    private function isExpectedParameterSignature(
        ClassReflection $classReflection,
        ReflectionMethod $method,
    ): bool {
        if ($method->getNumberOfParameters() !== 1) {
            return false;
        }

        $eventParameter = $method->getParameters()[0];

        if ($eventParameter->getName() !== 'event') {
            return false;
        }

        $eventParameterType = $eventParameter->getType();

        if (
            !$eventParameterType instanceof ReflectionNamedType
            || $eventParameterType->getName() !== $this->eventArchitecture->getSubscribedEventName($classReflection)
        ) {
            return false;
        }

        return true;
    }

    private function isExpectedMethodName(
        ClassReflection $classReflection,
        ReflectionMethod $method,
    ): bool {
        $expectedName = $this->eventArchitecture->getExpectedSingleEventSubscriberMethodName($classReflection);

        return $method->getName() === $expectedName;
    }
}
