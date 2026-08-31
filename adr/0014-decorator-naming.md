# Decorator naming conventions

## Context

A decorator implements the same contract as another object, delegates to that object and
adds behaviour. Without a shared naming convention, decorator classes may use a generic
`Decorator` suffix, while the decorated dependency may be called `$inner`, `$wrapped`,
`$delegate` or merely repeat the contract name.

Infection names symbols after their domain role and behaviour, not their technical kind or
the pattern used to implement them. An interface does not receive an `Interface` suffix and
an exception does not receive an `Exception` suffix. Likewise, `Decorator` identifies an
implementation pattern, not what a class does. Encoding it in the class name exposes a
structural detail while leaving callers to discover the useful behaviour elsewhere.

Infection's established decorators describe their added behaviour and retain the decorated
contract in the class name. For example, `MemoizedSourceCollector` memoizes a
`SourceCollector`, while `FileLocationReporter` adds file-location output to a `Reporter`.
Their decorated dependencies are named `$decoratedCollector` and `$decoratedReporter`.

The convention must distinguish the object being decorated from any other collaborator the
decorator needs. It must also avoid class and property names that describe the implementation
mechanism without explaining the object's role.

## Decision

Name a decorator `<Behaviour><Contract>`, where `<Behaviour>` describes what the decorator
adds and `<Contract>` is the short name of the contract it implements. Do not add a
`Decorator` suffix.

For example, a source collector that memoizes its result is named
`MemoizedSourceCollector`, not `SourceCollectorDecorator` or
`MemoizedSourceCollectorDecorator`.

Name the constructor-injected decorated object `$decorated<Role>`, where `<Role>` is the
principal role noun of the contract, such as `Collector`, `Reporter`, `Iterator`, `Loader`
or `Mutator`. For example:

```php
final readonly class MemoizedSourceCollector implements SourceCollector
{
    public function __construct(
        private SourceCollector $decoratedCollector,
    ) {
    }
}
```

Apply this property convention only to the object that the class decorates. Name additional
collaborators after their own roles. A factory or another value from which the decorated
object will later be created is not itself the decorated object and must not use the
`$decorated<Role>` name.

This decision applies to new and renamed decorators. Existing decorators should be aligned
when they are changed; adopting this convention does not by itself require unrelated
renames.

## Consequences

Decorator names communicate both their contract and the behaviour they add. The decorated
dependency is unambiguous among the class's collaborators, and code search for `decorated`
can find decorator relationships.

Some existing decorators do not follow this convention. Aligning them may cause internal
churn and, for public extension points, require a backward-compatibility assessment.

## Enforcement

This convention is enforced during review. Automated enforcement may be added if the
decorator relationship can be identified without false positives.

## Alternatives considered

Using a `Decorator` suffix makes the implementation pattern explicit, but naming symbols by
their technical kind is contrary to the project's naming conventions. As with `Interface`
and `Exception`, the suffix does not explain the symbol's domain role. A generic name such
as `SourceCollectorDecorator` also omits the behaviour it adds, while
`MemoizedSourceCollectorDecorator` retains the redundant implementation detail after the
behaviour and contract already identify the class.

Naming the dependency `$inner`, `$wrapped` or `$delegate` describes a structural detail but
omits the decorated contract. Naming it `$sourceCollector` does not distinguish it from an
ordinary collaborator. `$decorated<Role>` communicates both its role and purpose without
repeating the complete type name.

## Status

Proposed.
