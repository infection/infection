# Disambiguate positional source selectors

## Context

Positional arguments already select source paths and test paths. Source symbol
selection adds class names, methods, and absolute source lines to that interface.
A short class name such as `Differ` is also a valid path fragment. Classification
must work without loading the user's classes, including when a path contains
characters used by selectors.

## Decision

Readable files and directories take precedence over symbol parsing. Classify
them using the existing source-directory and test-path rules. Do not autoload
classes to classify an argument.

For unresolved arguments, parse selectors before applying path-fragment rules.
A bare PHP identifier such as `Differ` supplies both a path-substring filter and
a short-class-name selector. File filtering and symbol selection intersect:
only matching classes in matching files can produce mutations. Multiple symbol
selectors form a union within that file constraint.

Use a trailing slash for path-only interpretation (`Differ/`) and a trailing
backslash for short-class-only interpretation (`Differ\`). A leading backslash
makes a class name fully qualified, including the global class (`\Differ`).
Readable-path precedence applies to these forms too. Shell arguments containing
backslashes should be quoted.

Selectors may add a method, an absolute source line, or both, using `::`:
`Differ::diff`, `Differ::32`, and `Differ::diff::32`. Reject malformed explicit
selectors instead of silently interpreting them as paths.

## Consequences

Existing readable paths keep their meaning. Users can select symbols without
knowing their file paths or having an autoloader available.

Unresolved bare names change behaviour: `Differ` previously selected every file
whose path contained that text. It now also requires a class named `Differ`.
For example, `src/Differ/Helper.php` is excluded even though its path matches.
Use `Differ/` for a directory fragment or `--filter=Differ` to retain arbitrary
path-substring filtering. Use `Differ\` to select that short class name across
all source files, and `\Differ` to select only the global class.

The compact syntax introduces ambiguity and requires users to learn the markers.
A readable path can take precedence over a name the user intended as a selector;
a qualified symbol name normally resolves that conflict.

## Enforcement

`PositionalArgumentsClassifierTest` covers path precedence and the three
classification buckets. Parser, matcher, and visitor tests cover selector
syntax and node eligibility. The `Source_Symbol_Selection` end-to-end scenario
checks mutation generation for bare names and the explicit markers.

## Alternatives considered

Require explicit symbol syntax for every selector, such as a separate option
or a mandatory marker. That would preserve all bare path fragments and reduce
ambiguity, but would make common class and method selection more verbose.
The chosen syntax supports short positional selectors while providing explicit
forms for each interpretation.

Give symbols precedence over readable paths. That would make class selection
less dependent on the filesystem, but could reinterpret existing path arguments
and Windows paths. Preserving readable paths is the stronger compatibility rule.

Keep bare names as paths only. That would preserve their previous behaviour,
but a bare class name would then behave differently from its method selector.
The chosen intersection keeps the file constraint while enabling class selection.

## Status

Proposed.
