# Document recoverable exceptions

## Context

Any PHP statement can throw. Native functions, extensions, dependencies and user code invoked by
Infection can all introduce exceptions, including those not declared by their documented APIs.
Exhaustively listing every exception that might propagate from a method is therefore neither
possible nor useful. Such lists obscure the failures that callers can reasonably handle and become
stale when implementation details change.

The exception hierarchy communicates whether recovery is expected. A `LogicException` reports a
programming error or a violated internal invariant. An `Error` has the same recovery semantics: the
code must be corrected. It is equivalent to a compilation failure in a compiled language. A
`RuntimeException` reports a failure caused by the execution environment or input from which a
caller may reasonably recover.

The project currently uses `@throws` inconsistently. Some declarations document recoverable
failures, others document programming errors, and some enumerate exceptions propagated through
several implementation layers. Callers consequently cannot tell whether an `@throws` tag is part
of the method's contract or merely describes its current implementation.

## Decision

Use an `@throws` tag to document an exception when both of the following are true:

- The exception represents a failure from which the caller may reasonably recover.
- The exception is an intentional part of the callable's contract, whether thrown directly or
  deliberately allowed to propagate.

Such exceptions must have runtime semantics. They normally extend `RuntimeException`, directly or
indirectly. Document each exception type with a separate `@throws` tag. Add a short description when
the condition that causes it is not already evident from the callable's contract.

Do not document:

- `LogicException`, `Error`, or their subclasses, because callers are not expected to recover from
  programming errors or violated invariants.
- Incidental exceptions that native PHP code, dependencies, callbacks or other implementation
  details may throw.
- Every exception declared by a collaborator merely because it could propagate through the callable.

Document an exception on the declaration that defines the callable's contract. Implementations and
overrides rely on PHPDoc's implicit inheritance and must not repeat inherited `@throws` tags. An
implementation must document an additional recoverable exception if it intentionally expands the
inherited contract.

The distinction concerns the meaning of the failure, not whether a caller could technically catch
the throwable. Catching a `LogicException` or `Error` for logging, clean-up or conversion does not
make the exception recoverable or require an `@throws` tag.

### Examples

`Locator::locate()` exposes two expected lookup failures. A caller can correct the path or try
another location, so the canonical documentation lists them separately:

```php
/**
 * @throws FileNotFound When the requested file cannot be found.
 * @throws FileOrDirectoryNotFound When the requested file or directory cannot be found.
 */
public function locate(string $fileName): string;
```

`ParentConnector::getParent()` asserts that AST enrichment has already connected the node. A failed
assertion indicates an incorrect traversal or call order, so its `LogicException` is not documented:

```php
public static function getParent(Node $node): Node
{
    Assert::true($node->hasAttribute(self::PARENT_ATTRIBUTE));

    return $node->getAttribute(self::PARENT_ATTRIBUTE);
}
```

## Consequences

- `@throws` identifies failures that callers can usefully handle instead of attempting to catalogue
  every possible throwable.
- Refactoring an implementation does not require PHPDoc changes when only incidental propagated
  exceptions change.
- Adding or removing a documented exception changes the callable's failure contract and requires
  the same care as any other contract change.
- Existing declarations remain inconsistent until they are addressed separately. This decision
  does not require a codebase-wide cleanup.
- Exception classes with semantics that do not match their place in the hierarchy may need to be
  reclassified before their documentation can follow this convention.

## Enforcement

PHPStan and Mago can partially enforce this decision. Mago provides the
[`check-throws`][mago-check-throws] and [`unchecked-exceptions`][mago-exception-filtering] settings.
PHPStan provides [checked-exception configuration][phpstan-exceptions] for reporting missing or
overly broad `@throws` declarations and excluding unchecked exception classes.

Their configuration and existing baselines may later be tightened to enforce more of this decision
as the codebase's inconsistencies are addressed. Neither tool can determine whether a failure is
reasonably recoverable or an intentional part of a callable's contract. Review therefore remains
the primary enforcement mechanism.

## Alternatives considered

Documenting every exception that may escape a callable was rejected because PHP does not provide a
closed set of checked exceptions. The resulting lists would be incomplete, implementation-coupled
and difficult to maintain.

Documenting every explicitly thrown exception was rejected because it would present programming
errors as failures callers are expected to handle, while omitting deliberately propagated runtime
failures.

Never using `@throws` was rejected because recoverable failures are useful contract information and
allow callers to choose an appropriate response.

## Status

Accepted.

[mago-check-throws]: https://mago.carthage.software/tools/analyzer/configuration-reference/#feature-flags
[mago-exception-filtering]: https://mago.carthage.software/tools/analyzer/configuration-reference/#exception-filtering
[phpstan-exceptions]: https://phpstan.org/user-guide/exceptions
