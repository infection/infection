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

namespace Infection;

use Infection\Container;
use function array_key_exists;
use Closure;
use function count;
use Infection\TestFramework\Factory;
use InvalidArgumentException;
use function is_a;
use function Pipeline\take;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use function reset;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ContainerHelper
{
    /**
     * @var array<class-string<object>, object>
     */
    private array $values = [];

    /**
     * @var array<class-string<object>, Closure(Container): object>
     */
    private array $factories = [];

    private readonly Container $container;

    /**
     * @param iterable<class-string<object>, Closure(Container): object> $factories
     */
    public function __construct(array $factories, Container $container)
    {
        $this->factories = $factories;
        $this->container = $container;
    }

    /**
     * @param class-string<object> $id
     * @param Closure(Container): object $value
     */
    public function offsetSet(string $id, Closure $value): void
    {
        $this->factories[$id] = $value;
        unset($this->values[$id]);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @phpstan-return T
     */
    public function get(string $id): object
    {
        if (array_key_exists($id, $this->values)) {
            $value = $this->values[$id];
            Assert::isInstanceOf($value, $id);

            return $value;
        }

        if (array_key_exists($id, $this->factories)) {
            $value = $this->factories[$id]($this->container);

            return $this->setValueOrThrow($id, $value);
        }

        $value = $this->createService($id);

        if ($value === null) {
            throw new InvalidArgumentException(sprintf('Unknown service "%s"', $id));
        }

        return $this->setValueOrThrow($id, $value);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @phpstan-return T
     */
    private function setValueOrThrow(string $id, object $value): object
    {
        Assert::isInstanceOf($value, $id);
        $this->values[$id] = $value;

        return $value;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @phpstan-return ?T
     */
    private function createService(string $id): ?object
    {
        $reflectionClass = new ReflectionClass($id);
        $constructor = $reflectionClass->getConstructor();

        if (!$reflectionClass->isInstantiable()) {
            return null;
        }

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return $reflectionClass->newInstance();
        }

        $resolvedArguments = take($constructor->getParameters())
            ->map($this->resolveParameter(...))
            ->toList();

        // Check if we identified all parameters for the service
        if (count($resolvedArguments) !== $constructor->getNumberOfParameters()) {
            return null;
        }

        return $reflectionClass->newInstanceArgs($resolvedArguments);
    }

    /**
     * Builds a potentially incomplete list of arguments for a constructor; as list of arguments may
     * contain null values, we use a generator that can yield none or one value as an option type.
     *
     * @return iterable<object>
     */
    private function resolveParameter(ReflectionParameter $parameter): iterable
    {
        // Variadic parameters need hand-weaving
        if ($parameter->isVariadic()) {
            return;
        }

        $paramType = $parameter->getType();

        // Not considering composite types, such as unions or intersections, for now
        Assert::isInstanceOf($paramType, ReflectionNamedType::class);

        // Only attempt to resolve a non-built-in named type (a class/interface)
        if ($paramType->isBuiltin()) {
            return;
        }

        /** @var class-string $paramTypeName */
        $paramTypeName = $paramType->getName();

        // Found an instantiable class, done
        if ((new ReflectionClass($paramTypeName))->isInstantiable()) {
            yield $this->get($paramTypeName);

            return;
        }

        // Look for a factory that can create an instance of an interface or abstract class
        $matchingTypes = $this->factoriesForType($paramTypeName);

        // We expect exactly one factory to match the type, otherwise we cannot resolve the parameter
        if (count($matchingTypes) !== 1) {
            return;
        }

        yield $this->get(reset($matchingTypes));
    }

    /**
     * Retrieves the class or interface names of all registered factories that can produce instances of the given type.
     * This includes direct implementations, subclasses, or the type itself.
     *
     * @template T of object
     * @param class-string<T> $type the class or interface name to find factories for
     * @return class-string<T>[] a list of factory IDs (class-strings) that are compatible with the given type
     */
    private function factoriesForType(string $type): array
    {
        return take($this->factories)
            ->keys()
            ->filter(static fn (string $id) => is_a($id, $type, true))
            ->toList();
    }
}
