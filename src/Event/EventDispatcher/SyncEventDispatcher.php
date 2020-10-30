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

namespace Infection\Event\EventDispatcher;

use function get_class;
use Infection\Event\Subscriber\EventSubscriber;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class SyncEventDispatcher implements EventDispatcher
{
    /**
     * @var callable[][]
     */
    private array $listeners = [];

    public function dispatch(object $event): void
    {
        $name = get_class($event);

        foreach ($this->getListeners($name) as $listener) {
            $listener($event);
        }
    }

    public function addSubscriber(EventSubscriber $eventSubscriber): void
    {
        foreach ($this->inferSubscribedEvents($eventSubscriber) as $eventName => $listener) {
            $this->listeners[$eventName][] = $listener;
        }
    }

    /**
     * @return iterable|callable[]
     */
    private function inferSubscribedEvents(EventSubscriber $eventSubscriber): iterable
    {
        $class = new ReflectionClass($eventSubscriber);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            /** @var ReflectionMethod $method */
            if ($method->isConstructor()) {
                continue;
            }

            foreach ($method->getParameters() as $param) {
                $paramClass = $param->getType();
                Assert::isInstanceOf($paramClass, ReflectionNamedType::class);

                $closure = $method->getClosure($eventSubscriber);
                Assert::notNull($closure);

                // Returning a closure instead of array [$eventSubscriber, $method->name], should work the same
                yield $paramClass->getName() => $closure;

                break;
            }
        }
    }

    /**
     * @return callable[]
     */
    private function getListeners(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }
}
