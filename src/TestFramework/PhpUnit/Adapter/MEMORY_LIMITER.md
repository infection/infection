# PHPUnit mutant memory limiting

## Context

PHPUnit reports its memory usage during the initial test run. Infection uses that value to limit
PHPUnit mutant processes to twice the observed usage. This is a PHPUnit implementation detail:
other test-framework adapters, including static-analysis adapters used as follow-up stages, must not
inherit this limit.

Historically, `MemoryLimiter` appended the derived limit to the temporary `php.ini` created by
Composer XdebugHandler. That changed shared process state. In particular, PHPStan mutant processes
could inherit a limit derived from PHPUnit and fail before completing their analysis.

## Current design

`PhpUnitAdapter` records the memory usage reported by its initial run. When it builds a PHPUnit
mutant command, it asks its `MemoryLimiter` for PHP arguments and, when limiting is possible, adds:

```text
-d memory_limit=<twice the initial PHPUnit memory usage>M
```

The shared `php.ini` remains unchanged. Consequently, the limit applies to the PHPUnit mutant
process only and is not inherited by PHPStan or another adapter's processes.

`MemoryLimiter` and `MemoryLimiterEnvironment` deliberately live in the PHPUnit adapter namespace.
They are not test-framework contracts: another adapter may implement a different policy based on
the information and execution model of that framework.

The existing guards remain relevant. No derived limit is returned when:

- the initial PHPUnit memory usage could not be detected;
- the Infection process already has a memory limit; or
- Infection is using the system ini under phpdbg.

## Known wrapper limitations

PHP options can only be applied when Infection controls the PHP interpreter invocation. The command
builder cannot generally insert PHP options behind an opaque wrapper.

### Windows batch wrappers

For a `phpunit.bat` executable, `CommandLineBuilder` launches the batch file directly and drops PHP
arguments. Passing `-d memory_limit=...` to the batch file would pass an unknown argument to the
wrapper rather than to the PHP interpreter hidden inside it.

This means the derived memory limit is not applied when PHPUnit is launched through a batch
wrapper. The command builder already drops user-supplied PHP arguments in this case, but the old
shared-ini implementation could still affect the PHP process started by the wrapper. The current
adapter-local implementation therefore changes memory-limit behavior for this case.

### Executable Unix wrappers

When there are no PHP arguments, `CommandLineBuilder` may execute an executable PHPUnit wrapper
directly. Supplying the derived `-d` argument instead makes it select a PHP interpreter. This is
correct for an executable PHP script, including the usual Composer PHPUnit proxy, but can break an
executable wrapper implemented in Bash or another language by asking PHP to interpret it.

The `Phpunit_Bat_Wrapper` e2e fixture and `CommandLineBuilderTest` cover related wrapper behavior,
but they do not prove that the derived limit works through every wrapper type.

## Alternative considered: runtime `ini_set()`

Infection could set the limit from its generated per-mutant PHPUnit bootstrap:

```php
ini_set('memory_limit', '32M');
```

This would be local to PHPUnit and would work independently of batch or shell wrappers. It does not,
however, exactly preserve startup-time enforcement. `memory_limit` is runtime-configurable, but PHP
refuses to lower it below the memory already allocated by the process. In that situation `ini_set()`
emits a warning, returns `false`, and leaves the previous limit active. Code loaded before Infection's
generated bootstrap would also run without the derived limit.

The target is twice the memory reported by the initial PHPUnit run, so refusal should be uncommon,
but it cannot be ruled out across PHPUnit versions, wrappers, extensions, and user bootstraps. For
that reason this alternative was not selected for the current refactoring.

## Porting checklist

When porting this work to the main branch:

1. Keep memory extraction and limiting private to the PHPUnit adapter.
2. Do not restore a shared `php.ini` mutation or a generic test-framework memory-limiter contract.
3. Verify that PHPUnit itself builds the mutant command; a transitional process factory may still
   hold a separate legacy adapter instance.
4. Verify the normal `Memory_Limit` e2e scenario.
5. Verify `PHPStan_Memory_Limit` proves that the temporary ini is untouched and PHPStan retains its
   own effective memory limit.
6. Exercise Windows batch and executable non-PHP wrapper paths before claiming complete behavioral
   equivalence with the shared-ini implementation.
7. If wrapper-safe enforcement becomes mandatory, reconsider a generated-bootstrap `ini_set()` or a
   process-scoped startup configuration and document the changed enforcement semantics.
