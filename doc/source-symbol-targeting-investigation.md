# Target a class, method, or line

Related: [#2237](https://github.com/infection/infection/issues/2237) and
[#3498](https://github.com/infection/infection/issues/3498), areas 1 and 3.

## Recommendation

Proceed with this feature as source collection plus AST eligibility selection. It does not
require changes to tracing, mutant evaluation, or process creation. The proof of concept in
this branch confirms that positional inputs can carry a typed source selector separately
from source and test paths, and that an enriched AST can match a class, method, and absolute
source line.

## Proof-of-concept status

Implemented:

- `SourceSymbolSelector` represents a class, optional method, and optional absolute line.
- `SourceSymbolSelectorParser` parses positional selectors without invoking the project
  autoloader.
- `PositionalPathsClassifier` now returns selectors in a third `ClassifiedPaths` bucket;
  existing files take precedence, which prevents Windows paths from being parsed as symbols.
- `SourceSymbolMatcher` matches enriched AST nodes by their containing named class and
  method, and uses inclusive node ranges for line matching. This includes multiline nodes
  and method signatures.
- `ExcludeNonSelectedSourceNodesVisitor` demonstrates how non-matching nodes can be made
  ineligible after name and parent enrichment.

Not yet wired:

- selectors are not resolved to source files;
- the exclusion visitor is not registered in `NodeTraverserFactory` or the container;
- `run` and `debug:dump-ast` do not yet pass selectors into AST enrichment;
- missing classes, methods, and out-of-class lines are not validated;
- no command help, e2e scenario, or infection/site documentation has been added.

Consequently, `make autoreview` currently reports `ClassifiedPaths::$sourceSelectors` as an
unread property. This is the expected boundary of the proof of concept: production wiring
must consume the bucket. The finding is neither suppressed nor added to a baseline.

This boundary is deliberate. The active branch changes container and runner wiring, while
the proof of concept can establish feasibility without colliding with those changes.

## Grammar established by the proof of concept

The accepted forms are:

```text
Vendor\Package\Class
\Vendor\Package\Class
Vendor\Package\Class::method
Vendor\Package\Class::32
Vendor\Package\Class::method::32
```

The leading namespace separator is normalized away. A numeric coordinate is an absolute,
one-based source-file line. `__invoke` is an ordinary method name. A single-colon form such
as `Class::method:32` is rejected rather than accepted as an alias: accepting two spellings
would make the durable CLI contract needlessly ambiguous. Mutator suffixes and namespaced
functions are outside the first grammar.

The parser requires a namespaced class name. This keeps existing bare values such as
`Mailer` and `Mailer.php` compatible with file filtering. Existing filesystem entries are
classified before symbol parsing, including paths containing backslashes or drive-letter
colons.

Traits and enums use the same named class-like AST representation and can use this grammar.
Anonymous classes cannot be selected by name. Multiple declarations in a file are handled
by matching the nearest containing class-like node. Inherited methods require an explicit
resolution decision: selecting a child class could target the declaring parent file or fail
because the method is not declared in the selected class. The first production slice should
choose and validate one behaviour rather than silently producing no mutations.

## Verified architecture

`PositionalPathsClassifier` is the existing extension seam. Positional source paths become
source filters and positional test paths become test-framework arguments. The deprecated
`--filter` remains file-only and should not gain symbol syntax.

File collection and in-file constraints are distinct. Source files are collected through
`Configuration\SourceFilter` and `Source\Collector`; line constraints implement
`SourceLineMatcher` and are consumed by `ExcludeUnchangedLinesVisitor`. Name resolution,
parent connection, and reflection already run before line exclusion in
`NodeTraverserFactory`. A symbol exclusion visitor therefore belongs after those enrichment
visitors and before `AddTestsVisitor`, preserving lazy coverage attachment and the visitor
order invariant.

Line matching must use inclusive `[startLine, endLine]` ranges. Checking only a node's start
line misses multiline expressions. Method matching walks to the containing `ClassMethod`,
so signature nodes are included and remain candidates for function-signature mutators.

## Next implementation slices

1. Resolve every selected class to its declaring source file before collection. Composer's
   class map and PSR mappings should be preferred over loading the class, because reflection
   can execute project autoload code. Define a deterministic fallback and actionable unknown
   class/outside-source errors.
2. Carry resolved selectors through run configuration and construct a matcher per source
   file. Keep this wiring isolated from the ongoing container rework.
3. Register symbol exclusion after reflection and before `AddTestsVisitor`, then demonstrate
   eligibility changes through `debug:dump-ast`.
4. Validate absent methods and lines outside the selected class. An empty match must not
   silently degrade to an empty mutation run.
5. Add command/configuration coverage, one e2e scenario, CLI help, and infection/site
   examples. A CLI-only first release requires no schema change.

## Risks and decisions still open

- Reflection-based discovery may execute application code; static Composer metadata is safer
  but does not cover every custom autoloader.
- Class and method names are case-insensitive in PHP. The proof-of-concept matcher follows
  that rule, while the resolver must still return one deterministic declaration.
- Eligibility remains a boolean and cannot explain whether coverage, ignores, or a selector
  excluded a node. The selection-reasons work proposed separately would improve diagnostics.
- Combining multiple selectors needs explicit union semantics, including selectors for the
  same file.
- Mutator-qualified selectors and ignore configuration are separate features and should not
  be added to this grammar's first release.

This is a durable user-facing CLI contract with credible grammar and resolution alternatives,
so it remains an ADR candidate. Search `adr/` and update or supersede an existing decision
before proposing a new record; do not write the ADR as part of this proof of concept.
