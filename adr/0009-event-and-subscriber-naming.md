# Event and subscriber naming conventions

## Context

Events describe facts that have already occurred. Naming them as commands, requests or
ongoing actions obscures this distinction and makes their place in the execution lifecycle
less clear. A past-tense name such as `MutationTestingWasStarted` makes the event's nature
and place in the lifecycle explicit.

The current naming convention was established in [#1022][1022], which renamed the existing
events to follow the `<Subject>Was<PastParticiple>` form and used it for the new
application-execution events.

Subscribers need a stable, discoverable contract for each event. Using the generic
`EventSubscriber` marker interface alone would leave concrete subscribers free to choose
arbitrary method names and signatures. Finding every subscriber for an event would also
depend on the dispatcher's implementation details.

The typed single-event subscriber interfaces were introduced in [#2889][2889]. Before
then, subscribers implemented only `EventSubscriber` and had to follow the dispatcher's
method-naming convention manually. The accepted pattern made this convention an explicit
contract, enabling static analysis and IDE refactoring to track an event name alongside its
subscriber method.

A concrete subscriber may react to several related events. Its name should describe the
behaviour it provides, such as `MutationGenerationLoggerSubscriber`, rather than merely
repeat the name of an event it consumes.

## Decision

Name events as past-tense facts using the form `<Subject>Was<PastParticiple>`.
Examples include `ApplicationExecutionWasStarted`, `InitialTestCaseWasCompleted` and
`MutationTestingWasFinished`. Retain the established `Was` form instead of shortening event
names to `<Subject><PastParticiple>`.

Place event classes under `src/Event/Events`, grouped in namespaces that represent the
execution phase or domain to which they belong.

For each event, declare a co-located single-event subscriber interface with the following
form:

```php
interface MutationTestingWasFinishedSubscriber extends EventSubscriber
{
    public function onMutationTestingWasFinished(MutationTestingWasFinished $event): void;
}
```

The interface must:

- have the event's name followed by the `Subscriber` suffix;
- extend `EventSubscriber`;
- declare exactly one public, non-static method;
- name that method `on<EventName>`;
- accept exactly one parameter named `$event`, typed as the corresponding event;
- declare `void` as its return type.

Only events and their single-event subscriber interfaces belong under
`src/Event/Events`.

Name concrete subscribers after the behaviour they provide and give them the `Subscriber`
suffix, for example `MutationGenerationLoggerSubscriber`,
`StopInfectionOnSigintSignalSubscriber` or
`ReportAfterMutationTestingFinishedSubscriber`. They implement one or more single-event
subscriber interfaces, making every event they consume explicit in their declaration.

PHPat enforces the event-to-interface pairing and the required form of single-event
subscribers through
`tests/Architecture/PHPat/EventClassesShouldFollowConventionsTest.php`.

## Alternatives considered

Event names that omit `Was`, such as `MutationTestingStarted`, were considered. They offered
no clear benefit over the established convention. Changing the existing event names and
introducing a second naming style was therefore unjustified.

## Status

Accepted.

[1022]: https://github.com/infection/infection/pull/1022
[2889]: https://github.com/infection/infection/pull/2889
