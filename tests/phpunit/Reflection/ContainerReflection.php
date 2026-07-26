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

namespace Infection\Tests\Reflection;

use Closure;
use Error;
use Infection\Container\Container;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use function str_starts_with;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException as AssertException;

final readonly class ContainerReflection
{
    /** @var ReflectionClass<Container> */
    private ReflectionClass $reflection;

    private Closure $createServiceClosure;

    private Closure $getServiceClosure;

    private ReflectionProperty $factories;

    private ReflectionProperty $values;

    public function __construct(private Container $container)
    {
        $this->reflection = new ReflectionClass($container);

        $parentReflection = $this->reflection->getParentClass();
        Assert::notFalse($parentReflection);

        $this->factories = $parentReflection->getProperty('factories');
        $this->values = $parentReflection->getProperty('values');

        $this->createServiceClosure = $parentReflection->getMethod('createService')->getClosure($container);
        $this->getServiceClosure = $parentReflection->getMethod('get')->getClosure($container);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @phpstan-return ?T
     */
    public function createService(string $id): ?object
    {
        return self::handleCommonErrors($this->createServiceClosure, $id);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @phpstan-return ?T
     */
    public function getService(string $id): ?object
    {
        return self::handleCommonErrors($this->getServiceClosure, $id);
    }

    /**
     * @return array<class-string<object>, Closure>
     */
    public function getFactories(): array
    {
        return $this->factories->getValue($this->container);
    }

    /**
     * @param class-string $id
     */
    public function unsetFactory(string $id): void
    {
        foreach ([$this->factories, $this->values] as $property) {
            $value = $property->getValue($this->container);

            unset($value[$id]);
            $property->setValue($this->container, $value);
        }
    }

    /**
     * @return iterable<string, class-string>
     */
    public function iterateExpectedConcreteServices(): iterable
    {
        foreach ($this->reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (!str_starts_with($method->getName(), 'get')) {
                continue;
            }

            $returnType = $method->getReturnType();

            if (!$returnType instanceof ReflectionNamedType || $returnType->isBuiltin()) {
                continue;
            }

            /** @var class-string $returnTypeClassName */
            $returnTypeClassName = $returnType->getName();

            $typeReflection = new ReflectionClass($returnTypeClassName);

            if ($typeReflection->isInterface()) {
                continue;
            }

            yield $method->getName() => $returnTypeClassName;
        }
    }

    /**
     * @template T of object
     *
     * @param callable(class-string<T>):?T $callable
     * @param class-string<T> $id
     * @phpstan-return ?T
     */
    private static function handleCommonErrors(callable $callable, string $id): ?object
    {
        try {
            return $callable($id);
        } catch (Error|AssertException) {
            // Ignore services that require extra configuration (cause errors or assertions without it)
            return null;
        }
    }
}
