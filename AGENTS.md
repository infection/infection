# AGENTS.md

Operating guide for AI agents contributing to Infection. It is read once per session, so it is
dense on purpose: every line earns its place, and most lines exist because an agent's default
instinct was observed to be wrong here. Claims are anchored to files - trust the tree over
your training data: this codebase was heavily re-architected through 2025-2026 and your
priors about it are stale. This file rots like everything else: when your change makes a
line here false - a path moves, a version gate shifts, a convention evolves - updating that
line is part of the same task, not a separate chore. If you catch a stale line while merely
reading, fix it too.

## What you are working on

Infection is the mutation testing framework for PHP - 29 million Packagist installs, running
inside the CI of thousands of projects. It parses source into ASTs (nikic/php-parser - API
refresher: `vendor/nikic/php-parser/README.md` and its `doc/` folder), applies
small mutations, and checks whether the project's tests - and optionally a static analyser -
notice. A bug here silently corrupts other projects' quality gates. A slowdown here multiplies
by thousands of mutant processes per user run. Reviewers weigh correctness, memory, and
per-process overhead equally.

The project dogfoods hard: CI runs Infection against itself with a minimum-MSI gate
(`.github/workflows/mt.yaml`) and annotates escaped mutants inline on PRs. That single fact
explains most of the house style below: code is shaped so that every mutation of it dies.

## Vocabulary

The canonical glossary (with citations to the mutation-testing literature) is transcluded
below. Use its terms in code, tests, and PR titles; reviewers rename code that does not.
Two Infection-specific notes on top of it: a `Mutation` object is a *serializable*
description of a change (created during analysis, applied later, possibly in another
process), and a mutator is concretely `canMutate()` + `mutate()`.

@doc/nomenclature.md

## The execution pipeline

One pass, phase by phase (diagram: `doc/nomenclature.md#execution-phases`):

1. **CLI entry** - `bin/infection`, `src/Command/RunCommand.php`. Parses options, then binds
   them into the container via `Container::withValues(...)`.
2. **Container** - `src/Container/Container.php` (~1,260 lines). One flat registry of lazy
   closure factories plus a typed getter per service. Built on `sanmai/di-container`.
3. **Engine** - `src/Engine.php` orchestrates everything below.
4. **Source collection** - `src/Source/` (`SourceCollector` implementations, including the
   git-diff collector); positional CLI paths classified into source vs test files by
   `src/Configuration/PositionalPathsClassifier.php`.
5. **Artefact collection** - initial test run with coverage
   (`src/Process/OriginalPhpProcess.php` re-enables xdebug/pcov for exactly this child), then
   coverage-xml + junit.xml ingestion into lazy `Trace` objects
   (`src/TestFramework/Coverage/`, `src/TestFramework/Tracing/`).
6. **AST parsing + enrichment** - `src/PhpParser/NodeTraverserFactory.php` runs a single
   ordered visitor stack that labels eligibility, resolves names, connects parents and next
   statements, attaches reflection and lazy covering-test lookups, and marks ineligible
   whatever is unchanged in the git diff, user-ignored, or untested.
7. **Mutation generation + heuristic suppression** - `src/Mutation/FileMutationGenerator.php`,
   `src/Mutator/NodeMutationGenerator.php`; mutators live under `src/Mutator/<Category>/`.
8. **Mutant materialisation + evaluation** - `src/Mutant/MutantCodeFactory.php` splices the
   replacement node by token positions and prints a minimal diff;
   `src/Process/Runner/ParallelProcessRunner.php` streams mutant processes at N threads; an
   escaped mutant may get a follow-up static-analysis process
   (`src/StaticAnalysis/` - PHPStan and Mago adapters).
9. **Reporting** - metrics in `src/Metrics/` (Welford-based running variance for timings),
   loggers/reporters in `src/Logger/` and `src/Reporter/` (legacy) plus the newer
   `src/Report/` framework that is gradually replacing them.

## Repo map

- `src/` - production code. PSR-4 `Infection\`. Nothing under `src/` may depend on `tests/`
  or benchmarks (PHPat-enforced); shipped test helpers live in `src/Testing/` for
  this reason.
- `tests/phpunit/` - unit/integration tests, mirroring `src/` one-to-one. Every concrete
  source class must have a canonical test named after it (PHPat rule).
- `tests/Architecture/PHPat/` - architecture and convention fitness rules (finality, `@internal`, canonical
  tests, IO-vs-`integration`-group, event conventions, src-not-depending-on-tests). They run
  under PHPStan via `devTools/phpstan.neon`, not under PHPUnit. The selectors' own tests are
  in `tests/phpunit/Architecture/`.
- `tests/phpunit/AutoReview/` - convention tests run by `phpunit_autoreview.xml`: mutator
  API shape, Definition presence, env-var hygiene, Makefile consistency, no mutable public
  properties (DTO whitelist in `tests/phpunit/AutoReview/ProjectCode/ProjectCodeProvider.php`).
- `tests/e2e/` - one directory per scenario; anatomy, the `tests/e2e_tests` runner, and the
  `tests/add_new_e2e` scaffold are covered by CONTRIBUTING.md, transcluded at the end of
  this section. On top of that: fixtures pin `"threads"` (usually 1 - parallel output is
  non-deterministic; the SA-integration scenarios deliberately use 4), and self-contained
  scenarios also run via `--group e2e`.
- `tests/benchmark/` - PHPBench suites (mutation generation, git-diff parsing, tracing).
  Performance PRs cite before/after numbers from these.
- `devTools/` - `phpstan.neon` (+ baseline), `mago-baseline.toml`, Docker bits. Baselines
  are for pre-existing debt only; never baseline a finding your new code introduced.
- `doc/` - `nomenclature.md`, `benchmarking.md`. User docs are NOT here - they live in the
  separate repo github.com/infection/site.
- `resources/schema.json` - the infection.json5 schema; every mutator is listed here.
- Vendored-with-intent: `src/Differ/UnifiedDiffOutputBuilder.php` (sebastian/diff fork,
  excluded from CS so upstream's header survives). Mark any code copied from upstream with a
  `// Infection specific` comment so it is greppable.

The human contributor guide - e2e scenario anatomy, the e2e runner, the pre-push hook - is
transcluded here:

@.github/CONTRIBUTING.md

## Commands

```bash
composer install
make help                 # source of truth for targets
make cs                   # PHP-CS-Fixer; ALWAYS this, never hand-format
make autoreview           # cs-check + PHPStan(+PHPat) + Mago + composer validate
                          #   + AutoReview suite + Rector dry-run + collision detector
                          #   + zizmor (locally only; CI skips it here)
make test-unit            # default group, e2e excluded
make test-unit-parallel   # paratest WrapperRunner
make test-e2e             # PHPUnit-group e2e + scripted scenarios
make test-infection       # dogfood: Infection on itself (needs the PHAR)
make compile              # build the scoped PHAR (Box + PHP-Scoper)

vendor/phpunit/phpunit/phpunit tests/phpunit/Path/To/SomeTest.php   # one file
vendor/phpunit/phpunit/phpunit --filter test_method_name           # one method
./bin/infection --threads=max                                      # run from source
./bin/infection describe Plus                                      # explain a mutator
```

`make autoreview` green is the definition of done - maintainers repeat this verbatim to AI
contributors. PHP floor is 8.3 (`composer.json` platform); Psalm is gone (2026), the static
analysers are PHPStan and Mago.

## House style: where your defaults are wrong

Each entry: what you will be tempted to write, what this codebase does instead, and why.
These were measured by having agents design the subsystems blind and diffing against reality.

### Finality: `final` keyword vs `@final` docblock

You will mark everything `final`. Here, hard `final` is for classes never mocked (mutators,
visitors, value objects, leaf utilities); services that tests mock carry `/** @internal
@final */` with NO keyword (e.g. `src/Mutation/Mutation.php`,
`src/Configuration/ConfigurationFactory.php`). The PHPat finality rule accepts either form;
adding the keyword to a mocked class breaks the suite. A third form exists for special cases:
`ParallelProcessRunner` is plain `@internal` with a dedicated PHPat exemption whose test name
states the reason ("intentionally non-final only to allow PHPUnit partial mocks",
`tests/Architecture/PHPat/ClassesShouldBeFinalTest.php`). Mockability is the only sanctioned reason for `@final` - if no test mocks the class,
use the keyword. Reviewers ask "is there a reason this is `@final` rather than `final`?" -
have the answer ("it is mocked in X").

### `@internal` everywhere; the public API is a whitelist

Every class gets `@internal` (PHPat-enforced). The only extension points users may depend on
are listed in `tests/phpunit/AutoReview/ProjectCode/ProjectCodeProvider.php::EXTENSION_POINTS`:
`Mutator`, `Definition`, `MutatorCategory`, `BaseMutatorTestCase`, `MutationAnalysisLogger`,
`SchemaConfigurationFactory`, `SchemaConfigurationFileLoader`, `SchemaValidator`. Extension
points must have documented doc-blocks (another PHPat rule). Everything else may break at any
release - and conversely, changing anything on that list is a BC event.

### Imports: everything, including functions and constants

Every native call is imported (`use function sprintf;`, `use const DIRECTORY_SEPARATOR;`) -
`make cs` does this for you. What no tool decides for you is failure semantics: for
functions that return `false` on failure, use the `thecodingmachine/safe` wrapper:
`use function Safe\preg_match;` - the PHPStan safe-rule rejects raw ones. When you genuinely want the
false-returning behavior, alias it loudly:
`use function ini_get as ini_get_unsafe;` (`src/TestFramework/Coverage/CoverageChecker.php`).
History note: Safe was removed in 2023 and deliberately re-adopted (v3) in 2025 - do not
"clean it up".

### Types do the checking; asserts explain failures

PHP 8.3 typed constants (`private const int PCRE_LIMIT = 30_000;`), `#[Override]` on every
override, `readonly` by default (`final readonly class` for value objects - but NOT readonly
where the class memoizes, e.g. `MutationConfigBuilder` caches its XPath). PHPStan-level types
are expected in docblocks: `positive-int`, `non-empty-string`, `list<>`, `class-string<Mutator<Node>>`,
`positive-int|'max'`. Runtime invariants use `webmozart/assert` with messages that say what
bug a failure would indicate:

```php
Assert::integer($threadIndex, 'Thread index cannot be null. This indicates a bug - verify the isEmpty() check...');
```

Do not add an assert for what static analysis already proves - reviewers ask "isn't SA
picking up on that?".

### Naming contracts

`find*` returns nullable; `get*` asserts and returns non-null (`ParentConnector::findParent()`
vs `::getParent()`; `ReflectionVisitor::findReflectionClass()`). Named constructors for
domain meaning: `Container::create()`, `ChangedLinesRange::forLine()`,
`NoSourceFound::noExecutableSourceCodeForDiff()`. Exceptions: domain-named, no `Exception`
suffix, extend the semantically right SPL class (`FileNotFound extends RuntimeException`,
`MinMsiCheckFailed extends UnexpectedValueException`), grouped marker interfaces under
`Throwable/` dirs. Names must describe the contract, not the implementation - reviewers
renamed `OutputFormatter` to `MutationAnalysisLogger` and rejected `MarkdownTextFileLogger`
because the output was not Markdown.

### Laziness is the architecture, and generators are single-pass

Expensive values hide behind `sanmai/later`: `lazy(generatorFn())` returns a memoized
`Deferred`; `->get()` is safe to call repeatedly, unlike a raw generator which silently
yields nothing the second time (`src/TestFramework/Coverage/XmlReport/XmlCoverageParser.php`).
Collection flows use `sanmai/pipeline`: `take($x)->filter(...)->cast(...)->toList()`.
Mind the naming - it inverts your instinct: `cast()` is the strict 1:1 mapper
(`array_map`); `map()` is 1:N - its callback may yield any number of values per input,
and a returned generator is flattened into the stream; `tap()` is for side effects.
Full API: `vendor/sanmai/pipeline/README.md`. Streams of work are non-rewindable generators BY DESIGN
- `ParallelProcessRunner` converts iterables to a generator precisely so a rewind fails
loudly, while letting `Iterator` test doubles pass through. Do not buffer, count, or rewind a
mutant stream. For hot paths, by-reference returns are a sanctioned idiom with a comment
(`TestLocations::&getTestsLocationsBySourceLine()` - junit enrichment mutates in place;
an immutable rebuild would silently no-op).

### Statics are a feature

Const-only and pure-static classes use the `use CannotBeInstantiated;` trait
(`src/CannotBeInstantiated.php`, ~30 users: `ProfileList`, `Console\XdebugHandler`).
Pure transforms are `private static` helpers or all-static classes (`FilterBuilder`) - DI is
reserved for collaborators with state or IO. Memoization is a `static $cache = []` inside an
intent-named method (`isPhpUnit10OrHigher()`), not a cache service.

### Time, filesystem, process

Never call `usleep`/`microtime` - inject `sanmai/duoclock`'s `DuoClock` (tests use a
`TimeSpy` and assert exact sleep arguments). Never touch the disk directly - inject
`Infection\FileSystem\FileSystem`; note `Symfony\Finder\SplFileInfo::getContents()` does NOT
cache (there is a `FileStore` decorator for that). For subprocesses, commands are
`list<string>` argv arrays end to end - never a joined shell string, and know that Symfony
`Process` does NOT split array elements on whitespace: `'php /path/to/composer'` is ONE
(broken) token. User-supplied option strings must go through the existing raw-token parsers.

### Comments and docblocks

Comment the WHY - reviewers block non-obvious version gates and workarounds until a rationale
comment exists ("can we add a reason here as it will not be clear in some time"). The best
files carry paragraph-length comments justifying mechanics (the ASCII commit graph in
`ConfigurationFactory::refineGitBase()`, the polling math in `ParallelProcessRunner`).
Keep `@psalm-mutation-free` on pure mutator methods - retained deliberately as purity
documentation even though Psalm itself left the toolchain; never add `@psalm-suppress`. No license header by hand - `make cs` stamps
the BSD-3-Clause block on every file.

### Shape code so mutants die

This is the style rule that surprises agents most. Code is structured for killability:

- Separate `return` statements instead of one combined boolean, so coverage shows which
  branch is exercised and mutants are traceable to a line.
- Early returns over `else`/`elseif` (reviewers ask for this by name).
- Magic values become named constants - constants are not mutated, so extracting a literal
  is the accepted fix for an unkillable mutant ("testing constants is going to be silly").
- Mock expectations assert exact arguments (`->with([...])`) - loose mocks let mutants
  escape and the self-mutation CI gate flags them on your PR.
- No default parameter values in constructors ("increases the risk of forgetting to pass
  it"). Data providers, by contrast, SHOULD use defaults + named arguments so each case
  states only what it exercises.
- Infection's own config globally skips mutating `Assert::` calls
  (`infection.json5` `global-ignoreSourceCodeByRegex`) - assertion messages are not worth
  killing.

An escaping mutant means either a missing test or code that should be reshaped; "only
performance" escapes can be accepted, but say so explicitly in the PR.

## Subsystem invariants

The ten places a confident rewrite goes wrong. Read the target file's comments before
touching any of these; each has tests that encode the invariant.

### 1. AST enrichment visitor stack - order is the algorithm

`NodeTraverserFactory::createEnrichmentTraverser()` registers ~10 visitors in ONE traverser;
correctness lives in the registration order (NameResolver and ParentConnecting before
ReflectionVisitor; git-diff exclusion before AddTests; AddTests before ExcludeUntested).
Visitors communicate through node attributes but you never touch `getAttribute()` directly -
each visitor owns its `public const string` key plus static typed accessors
(`AddTestsVisitor::getTests()`, `LabelNodesAsEligibleVisitor::isEligible()`). Exclusion
NEVER removes nodes - it flips the `eligible` flag only; removing corrupts name resolution
and format-preserving printing (learned the hard way, PR #3039). `beforeTraverse` must reset
all visitor state - instances are reused across files. Coverage lookups attach as memoizing
closures, only to eligible nodes. `NextConnectingVisitor` is deliberately NOT nikic's
NodeConnectingVisitor: "next" means next executable statement in the same function scope
(statement-functions break the chain; expression closures restore it).

### 2. Coverage ingestion - lazy at two levels, enriched by reference

`IndexXmlCoverageParser` yields `SourceFileInfoProvider`s (nothing parsed yet); each provider
lazily loads its per-file XML; test-location extraction defers again behind `later`. All
coverage XPath queries need the `p:` namespace prefix and go through `SafeDOMXPath`
(`src/TestFramework/XML/`), which converts libxml false-returns into `InvalidXml`. Format
tolerance is ordered fallback ladders with version comments (PHPUnit <6 percentage strings,
trait vs class methods, four junit XPath shapes for PHPUnit/Codeception/PhpSpec/Behat) - the
comments citing versions are what keep the next cleanup from deleting a live branch.
JUnit enrichment rebuilds `TestLocation`s in place
via the by-ref accessor. Function-signature mutations resolve tests by method line-range, not
line (`TestLocator`, `LineRangeCalculator` - which also widens to the outermost array
literal).

### 3. ParallelProcessRunner - streaming, TEST_TOKEN, follow-up chain

The runner is a generator yielding *finished* containers while consuming a non-rewindable
generator of pending ones. Worker slots are 1-based and recycled; each child gets exactly
`['INFECTION' => '1', 'TEST_TOKEN' => $threadIndex]` - `TEST_TOKEN` is the paratest-style
convention user suites read to pick per-worker databases; the literal name and 1-basing are
API. Static-analysis follow-up for escaped mutants lives in `MutantProcessContainer` as a
chain (`hasNext()` gated on `DetectionStatus::ESCAPED`); the runner just re-enqueues - do
not teach the runner about analysers. `ProcessQueue::enqueueFrom()` returns MICROSECONDS
spent (not a count); the poll loop subtracts it from the sleep - producing work is the wait.
Memory is released by `unset()` of the container reference before freeing the slot.

### 4. PHPUnit XML manipulation - a version matrix you must not simplify

`XmlConfigurationManipulator` is ~15 small public methods, one edit each, composed by two
builders; the
`version_compare` cutoffs (5.2, 7.2, 7.3, 9.3, 10, 10.1, 11.0, 12.0) each carry a comment
linking the phpunit.xsd change - keep them. Hard rules: PHPUnit >= 12's coverage/`<source>`
config is authoritative - leave it untouched (#3043 regression); when the user has an
include filter and no source filtering is requested, preserve their config rather than
overwrite; never `realpath()` config paths (glob patterns break -
`src/TestFramework/PhpUnit/Config/Path/PathReplacer.php`); test suites may live at `/phpunit/testsuite` without the
wrapper. The per-mutant bootstrap is a generated PHP file using
`infection/include-interceptor` to swap the original file for the mutant at include time; it
re-loads the PHAR and derives the scoped namespace prefix - PHP-Scoper is why the mechanism
is packaged, not inlined.

### 5. Mutant code generation - token positions and a sacred original AST

`MutatorVisitor` matches the target node by `startTokenPos`/`endTokenPos` + node class -
not file positions, not object identity. `MutantCodeFactory` runs `CloningVisitor` as its
own pass BEFORE adding `MutatorVisitor` (the original AST must remain byte-identical; a test
dumps and compares it). Printing is `printFormatPreserving()` against original tokens;
`InfectionPrettyPrinter` overrides exactly one method to stop php-parser canonicalizing
backslash escapes in single-quoted strings - mutation diffs must be minimal and faithful to
the original bytes. `Mutation::getHash()` is
`md5(implode('_', [filePath, mutatorName, mutatorIndex, ...six plucked attributes]))` - a
user-facing re-run id; algorithm, field set, and order are a compatibility contract.
`MutationAttributeKeys::pluck()` throws if any of the six position attributes is missing.
Replacements are `MutatedNode` wrapping `int|Node|Node[]` - yes, a bare int is legal.

### 6. Container - one registry, typed getters, no framework

All ~60 service factories live in one static `create()` array keyed by class-string; every
service has a typed getter (`getFileSystem(): FileSystem`); consumers never call `->get()`
directly. Wiring is explicit closures - reflection autowiring exists only as a fallback for
zero-config leaves. Laziness is closure+memoize; there are no proxies and there will be no
symfony/di or php-di (supply chain + PHAR scoping). After CLI parsing, `withValues(...)`
(~40 named params, most defaulting to `DEFAULT_*` consts) CLONES the container and re-binds
config-derived services; option values live in the one `Configuration` object, never as
container entries. Tests replace services with `$container->set(Id::class, fn () => $double)`
or `cloneWithService()`. A meta-test asserts every registered factory is actually reachable -
no speculative services. When a closure grows, extract a `final readonly class FooBuilder
implements Builder` under `src/Container/Builder/` and register the class-string.

### 7. Configuration merging - precedence is per-field semantics

`ConfigurationFactory::create()` takes ~35 flat scalars (mirrored by the test input builder).
Tri-state options are `?bool`/`?float` merged with `??`; flags either source may force ON are
non-nullable and merged with `||` (`noProgress || ciDetected`) - unifying these is a silent
bug. `'max'` threads = `max(1, cores - 1)` - one core is deliberately left free. Log paths
absolutize against the CONFIG FILE dir (a 2025 BC break, #2455) except the
`FileReporter::ALLOWED_PHP_STREAMS` allowlist - reuse it, do not hand-roll `php://` checks.
`refineGitBase()` runs `git merge-base` so only the user's own commits get mutated. Private
helpers are named `retrieve*` (merge/default) and `refine*` (transform).

### 8. PHPUnit --filter building - degrade to everything, never to nothing

`FilterBuilder` (all static) fights PCRE's ~30k compiled-pattern limit with discrete
optimization levels: full -> drop data-provider keys -> drop class names -> return `[]`,
which means OMIT `--filter` and run the whole suite - a safe superset. Its twin invariant
lives in `PhpUnitAdapter::testsPass()`: "No tests executed!" counts as PASS, so an
over-filtered run is not misread as a kill. The pattern is unanchored `/a|b|c/` over SHORT
class names; data-provider ids are converted to the OLD textual form (`method with data set
"key"` / `#N`) regardless of the reporting PHPUnit's format. Break any of these and mutation
scores silently corrupt.

### 9. Mutator resolution - an order-sensitive mini-language

In `MutatorResolver`: keys are processed in order, later wins; `false` REMOVES everything a
key (including a nested profile) previously registered - the bool sentinel travels through
the recursion, not a top-level branch. Enable-only uses `??=` so re-listing never wipes
settings; setting merge is `array_merge_recursive(NEW, EXISTING)` followed by explicit
dedupe of `ignore` lists - direction and dedupe both matter. `global-ignore*` keys are
stripped in a pre-pass; a CLI `--mutators` override replaces the file's selection but
deliberately retains `global-ignoreSourceCodeByRegex` (`ConfigurationFactory`).
`ProfileList` is the single source of truth for the mutator universe - AutoReview derives
everything from it.

### 10. Xdebug lifecycle - getSkippedVersion is the truth

The master process restarts itself without xdebug (composer/xdebug-handler in PERSISTENT
mode - without `setPersistent()` the temp ini does not stick to child processes). After that restart `extension_loaded('xdebug')`
is FALSE while coverage still works: `XdebugHandler::getSkippedVersion()` is the source of
truth everywhere. `OriginalPhpProcess` brackets `parent::start()` with
`PhpConfig::useOriginal()` / `usePersistent()` and injects `XDEBUG_MODE=coverage` unless
pcov/phpdbg is the better driver. `CoverageChecker` decides upfront via seven signals -
including regexing `--initial-tests-php-options` for drivers only the child will load - and
throws `CoverageNotFound` with a heredoc listing copy-pasteable remedies. `MemoryLimiter`
appends `memory_limit = 2x observed` to the xdebug-handler TEMP ini only - guarded by
`MemoryLimiterEnvironment::isUsingSystemIni()`; and it must run AFTER static analysis
(PHPStan OOMs otherwise, #2427). Every step is best-effort: early-return guards for six
can't-do-this conditions, swallowed `IOException`.

## Adding a mutator

The one fully-worked workflow, because it is the most common contribution and every step is
machine-checked. Scaffold: `./bin/infection make:mutator` (templates in
`src/CustomMutator/templates/`). A complete mutator PR touches:

1. `src/Mutator/<Category>/<Name>.php` - categories mirror the `src/Mutator/` subdirectories
   and the profiles in `ProfileList::ALL_PROFILES` (@arithmetic, @boolean, @cast,
   @conditional_boundary, @conditional_negotiation, @equal, @extensions, @function_signature,
   @identical, @loop, @nullify, @number, @operator, @regex, @removal, @return_value, @sort,
   @unwrap).
2. `src/Mutator/ProfileList.php` - register in BOTH the `<CATEGORY>_PROFILE` const AND the
   `ALL_MUTATORS` name map. Machine-checked: AutoReview derives the mutator universe from it.
3. `resources/schema.json` - add `"<Name>": { "$ref": "#/definitions/default-mutator-config" }`
   in the alphabetized mutators block. NOT machine-checked - reviewers look for it, and
   without it a user config referencing the mutator fails schema validation.
4. `tests/phpunit/Mutator/<Category>/<Name>Test.php`.
5. A doc PR to github.com/infection/site (checkbox in the PR template; maintainers track
   missing ones with an `Awaiting Docs` label).

The class shape (see `src/Mutator/Arithmetic/Plus.php` for the canonical minimal example):

```php
/**
 * @internal
 *
 * @implements Mutator<Node\Expr\BinaryOp\Plus>
 */
final class Plus implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition { /* description, category, remedy, diff */ }

    /**
     * @psalm-mutation-free
     * @return iterable<Node\Expr\BinaryOp\Minus>
     */
    public function mutate(Node $node): iterable
    {
        yield new Node\Expr\BinaryOp\Minus($node->left, $node->right, NodeAttributes::getAllExceptOriginalNode($node));
    }

    public function canMutate(Node $node): bool { /* instanceof + context guards */ }
}
```

Notes that carry review weight:

- `Mutator`, `Definition`, `MutatorCategory` come from the external `infection/mutator`
  package (kept tiny so third parties can depend on it; exposed unprefixed in the PHAR).
  `MutatorCategory` is string constants, not an enum. The `Definition` remedy string tells
  users how to KILL the mutant - write it.
- AutoReview allows exactly four public methods (`getDefinition`, `getName`, `mutate`,
  `canMutate`; plus `getConfigClassName` for configurable mutators, whose constructor must be
  exactly `__construct(<ConfigClass> $config)`). Helpers go private.
- `canMutate()` guards are the craft: match function names case-insensitively via
  `$node->name->toLowerString()`; inspect context via `ParentConnector::findParent()`
  (walk ancestors when nesting matters). You must not produce: syntax errors or guaranteed
  fatals (always-killed noise - e.g. removing a cast that satisfies a declared type under
  strict_types); equivalent mutations (`*= 1`, `$a ?? null` swaps); infinite loops (never
  flip `++`/`--` when the parent is `Node\Stmt\For_`); or overlaps with another mutator
  (reviewers run De Morgan-style analysis - state your reasoning in the PR).
- New AST nodes take `NodeAttributes::getAllExceptOriginalNode($node)`; when rebuilding a
  node from parts, copy EVERY child including `attrGroups` or you silently delete attributes.
- Tests extend `Infection\Testing\BaseMutatorTestCase` (a shipped extension point):
  `#[DataProvider('mutationsProvider')]` + `$this->assertMutatesInput($input, $expected)`.
  A missing `$expected` asserts "does not mutate". Case names are specs:
  `yield 'It does not mutate X to prevent overlap with MatchArmRemoval' => [...]`. The
  harness wraps snippets in namespace+class+method so tests run the PRODUCTION traversal
  (global-scope code is never mutated) - do not build your own traverser.
- Include a per-mutator stats table (mutations/killed/escaped/timeouts) from a self-run in
  the PR body for anything touching mutation volume.

## Testing conventions

- Test methods read as sentences: `test_it_...` - the descriptive naming is on you (casing
  and `$this->` assertion style auto-fix); `webmozarts/strict-phpunit` is active.
- `#[CoversClass(X::class)]` on every test. PHPat enforces the IO boundary BOTH ways: a test
  doing IO must be `#[Group('integration')]`, a test doing no IO must NOT be. Tests are
  final/abstract/trait (PHPat), deterministic (no hard-coded `/unlikely-to-exist` paths -
  use `sys_get_temp_dir()` + `uniqid()`), and fast.
- Data providers: `public static function ...Provider(): iterable`, `yield 'descriptive
  sentence' => [...]`, named arguments with defaults for wide signatures (the #2304 pattern);
  don't share one provider between tests that use different subsets of its params.
- Complex setup gets hand-rolled immutable builders/fakes next to the test
  (`tests/phpunit/Configuration/ConfigurationFactory/ConfigurationFactoryInputBuilder.php`,
  a fake `Git` implementation) rather than deep mock graphs.
- Env vars: any test calling `putenv` must `use BacksUpEnvironmentVariables;`
  (AutoReview-checked).
- Visitors have a dedicated harness - read `tests/phpunit/PhpParser/Visitor/README.md`:
  extend `VisitorTestCase`, `addIdsToNodes()`, traverse with your visitor +
  `MarkTraversedNodesAsVisitedVisitor`, dump with the configured `NodeDumper`, `assertSame`
  the dump. Console commands use `CommandTester`.
- Do not write one-off probe scripts; write the unit test you would have wanted anyway.

## Pull requests and review culture

- Target `master`. Squash-merge: the PR TITLE becomes the commit subject. Current format is
  `type(scope): Sentence-case summary` (`feat`, `fix`, `refactor`, `perf`, `test`, `build`,
  `ci`, `docs`; scopes are component names: `ast`, `tracing`, `logger`, `phpunit`). Any BC
  risk upgrades the prefix to `feat!:`/`fix!:` and gets a `BC break` label plus an
  enumerated "Breaking Changes" section in the body. Bare imperative titles survive on
  headline features, but prefixed is the norm; an external review bot (CodeRabbit) checks
  titles - that gate lives GitHub-side, not in this repo.
- Small, dependency-chained PRs are the house unit of work: "Depends on #NNNN",
  "Extracted from #NNNN", draft-until-dependency-merges. Refactors NEVER mix with behavior
  changes - the most repeated review demand across four years. Deletion PRs are prized.
- Tests are non-negotiable: a bot requests changes on any src-touching PR without tests.
  Fixed bugs MUST carry a regression test. New user-visible behavior may warrant an e2e
  scenario - argue it either way.
- Labels are not decoration - release notes are generated from them: one type label
  (`Feature`, `Bugfix`, `Internal`, `Performance`, `BC break`, `DX` for developer
  experience, `IDX` for IDE/integration work) plus component
  labels (`Component / AST`, `TestFramework / PHPUnit`, ...). These are the GitHub-side
  label names maintainers apply; the in-repo PR template spells them lowercase. CHANGELOG.md is
  maintainer-curated at release time - touch it only for deprecations or BC breaks,
  `[BC BREAK!]`-prefixed, when asked.
- User-facing changes need a doc PR to github.com/infection/site (linked in the checklist);
  internal work does not.
- Review culture to anticipate: "explain WHY" (maks-rafalko) - undocumented non-obvious
  mechanics block; naming precision and honesty (theofidry) - expect suggestion-block
  bikeshedding on names; cost/benefit pragmatism - nano-optimizations get challenged,
  YAGNI abstractions get declined ("I don't see a value yet"); BC pragmatism - "we are on
  0.x" allows breaks, but each one is called out, weighed, and changelogged. New
  dependencies are license-vetted (BSD-compatible; a GPL transitive killed one).
- The MSI gate on changed files aims at ~100% but is soft: genuinely unkillable mutants can
  be bypassed BY DISCUSSION - say so in the thread rather than writing a contrived test.
- Before finishing any task: `make cs`, then `make autoreview`, then the relevant test
  groups. If CS is wrong, run `make cs` - never hand-edit style.

## Gotchas digest

The costliest traps, recollected:

1. Mark nodes ineligible; NEVER remove or replace nodes during enrichment.
2. Never rewind, count, or buffer a mutant/process generator; `Deferred` (sanmai/later) is
   the re-readable lazy primitive, a generator is not.
3. `TEST_TOKEN` (1-based) is the exact env var user suites depend on.
4. `Mutation::getHash()` recipe is a user-facing contract - do not touch.
5. PHPUnit >= 12 coverage config: hands off. And never `realpath()` user config paths.
6. "No tests executed!" is a PASS; a too-long `--filter` degrades to running EVERYTHING.
7. `extension_loaded('xdebug')` lies after the restart - ask `getSkippedVersion()`.
8. `src/` must never reference `tests/` (shipped helpers go in `src/Testing/`); new
   test-framework adapters must also be added to the PHAR bundle in the Makefile.
9. Symfony `Process` argv arrays: one element = one token; never pre-join, never assume
   splitting.
10. e2e fixtures pin `"threads"` (usually 1) for deterministic output; e2e composer.json
    pairs `minimum-stability: dev` with `prefer-stable: true`.
11. JIT makes mutation testing ~50% SLOWER; file-backed OPcache helps (measured: #2810).
    Benchmark before
    "optimizing" (`make benchmark`), and re-validate old perf hacks before extending them.
12. Escaped mutant on a literal? Extract a named constant. Escaped mutant on a loose mock?
    Tighten `->with(...)`. Truly unkillable? Discuss a bypass - do not fake a test.

## Maintaining this file

This file was distilled from two sources: agents designing subsystems blind and diffing
their instincts against the real code, and four years of PR review threads. That defines
the admission test for every line: would a competent agent, without it, do the wrong
thing? If the tree, a linter, or `make autoreview` already teaches it cheaply, it does not
belong here - this is not a reference manual, and length is only justified by prevented
mistakes.

- Anchor every claim to a file path; prefer stating the invariant and its WHY over the how.
  When a claim cannot anchor to a file (review culture, GitHub-side gates, history), anchor
  it to a PR number or name the person.
- Keep the shape: what you will be tempted to do, what the codebase does, why. One
  canonical snippet per concept is instruction; a second is reference-manual creep.
- Orientation sections (the pipeline, the repo map, the transcluded companions) are the one
  exception to the admission test: they are the map, judged by whether they orient, not by
  a prevented mistake. Keep them terse.
- The Gotchas digest is deliberate redundancy - the costliest traps stated twice BY DESIGN.
  Nowhere else may this file say the same thing twice.
- What an auto-fixing tool repairs for free needs no line here; what fails loudly and fast
  may earn a short one; what fails silently earns a long one.
- When a convention changes, replace the old line - append history only when the history
  itself prevents a wrong cleanup (see the Safe note).
- Deletion improves this file as often as addition. If removing a line would change no
  agent's behavior, remove it.
