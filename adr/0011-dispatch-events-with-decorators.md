# Dispatch operation events with decorators

## Context

Events often describe the lifecycle of an operation already represented by an interface,
such as parsing a file or producing a report. Dispatching these events from the
implementation mixes the operation with event infrastructure. It also requires every
implementation to know about the dispatcher, although event dispatch is an application-level
concern rather than part of the operation's contract.

The source-collection subsystem already uses decorators that implement the decorated
service's interface to provide cross-cutting behaviour. For example,
`MemoizedSourceCollector` implements `SourceCollector` and delegates `::collect()` to another
`SourceCollector`, while keeping the underlying collector unaware of memoisation.
Event-dispatching decorators use the same structure to keep the decorated implementation
unaware of the event system.

Not every event represents the boundary of a delegated call. Some events describe
milestones within an algorithm, such as processing each item in a generated stream. Only
the component that owns that algorithm can dispatch such events at the correct point. For
example, `MutationGenerator` dispatches `MutableFileWasProcessed` only after it has yielded
all mutations for one source file and collected their identifiers.

## Decision

When events describe the boundary of an operation exposed through an interface, dispatch
them from a decorator instead of the underlying implementation. The decorator must:

- implement the same interface as the decorated service;
- be named `EventDispatching<DecoratedType>`;
- accept the decorated service and `EventDispatcher` as explicit constructor dependencies;
- dispatch the start event before delegating the operation;
- delegate the original arguments exactly once and preserve its return value or exception;
- dispatch the completion event at the point defined by that event's semantics; and
- avoid adding behaviour unrelated to event construction and dispatch.

For example, an event-dispatching source collector delegates the complete operation while
surrounding it with source-collection events:

```php
final readonly class EventDispatchingSourceCollector implements SourceCollector
{
    public function __construct(
        private SourceCollector $decoratedSourceCollector,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    public function collect(): array
    {
        $this->eventDispatcher->dispatch(new SourceCollectionWasStarted());

        $sources = $this->decoratedSourceCollector->collect();

        $this->eventDispatcher->dispatch(
            new SourceCollectionWasFinished(count($sources)),
        );

        return $sources;
    }
}
```

Use `try-finally` when the completion event means that the operation has ended whether it
succeeds or fails. When the event represents only successful completion, dispatch it after
delegation.

```php
final readonly class EventDispatchingSourceCollector implements SourceCollector
{
    public function __construct(
        private SourceCollector $decoratedSourceCollector,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    public function collect(): array
    {
        $this->eventDispatcher->dispatch(new SourceCollectionWasStarted());

        try {
            $sources = $this->decoratedSourceCollector->collect();
        } finally {
            // Dispatch the completion event even if the operation failed.
            $this->eventDispatcher->dispatch(
                new SourceCollectionWasFinished(count($sources)),
            );
        }

        return $sources;
    }
}
```

Construct and apply the decorator in the container or the relevant composition root. Other
services depend on the decorated interface and remain unaware of whether event dispatching
is enabled.

Keep event dispatch in the component that owns the algorithm when an event describes an
internal milestone rather than the boundary of one delegated operation. Do not extract such
dispatch into a decorator if this would require duplicating the algorithm, buffering a lazy
stream or exposing implementation details through the interface.

## Consequences

- Core implementations remain independent of the event system.
- Event dispatch can be added, removed or tested without changing the decorated operation.
- Callers continue to depend on the original interface.
- Decorator tests can verify delegation and the exact event sequence independently.
- The container gains explicit decoration wiring.
- Events tied to internal algorithm milestones remain in orchestration code and therefore
  do not follow the decorator pattern.

## Enforcement

This convention is enforced during architecture review. Each event-dispatching decorator
must have a canonical PHPUnit test that verifies the delegated arguments and result, the
exact event payloads and order, and the applicable exception behaviour.

No PHPat rule enforces this convention.

## Alternatives considered

This ADR rejects injecting `EventDispatcher` directly into every implementation for events
at operation boundaries. Doing so couples the core behaviour to application-level event
infrastructure and repeats the same lifecycle mechanics across implementations.

This ADR also rejects dispatching every event from a decorator. A decorator can observe only
one interface boundary; events for internal milestones belong to the component that owns
the corresponding algorithm.

## Status

Proposed.
