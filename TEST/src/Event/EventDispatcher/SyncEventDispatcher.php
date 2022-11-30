<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\EventDispatcher;

use _HumbugBox9658796bb9f0\Infection\Event\Subscriber\EventSubscriber;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class SyncEventDispatcher implements EventDispatcher
{
    private array $listeners = [];
    public function dispatch(object $event) : void
    {
        $name = $event::class;
        foreach ($this->getListeners($name) as $listener) {
            $listener($event);
        }
    }
    public function addSubscriber(EventSubscriber $eventSubscriber) : void
    {
        foreach ($this->inferSubscribedEvents($eventSubscriber) as $eventName => $listener) {
            $this->listeners[$eventName][] = $listener;
        }
    }
    private function inferSubscribedEvents(EventSubscriber $eventSubscriber) : iterable
    {
        $class = new ReflectionClass($eventSubscriber);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->isConstructor()) {
                continue;
            }
            foreach ($method->getParameters() as $param) {
                $paramClass = $param->getType();
                Assert::isInstanceOf($paramClass, ReflectionNamedType::class);
                $closure = $method->getClosure($eventSubscriber);
                Assert::notNull($closure);
                (yield $paramClass->getName() => $closure);
                break;
            }
        }
    }
    private function getListeners(string $eventName) : array
    {
        return $this->listeners[$eventName] ?? [];
    }
}
