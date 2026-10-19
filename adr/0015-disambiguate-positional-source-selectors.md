# Disambiguate positional source selectors

## Context

Infection accepts source and test paths as positional arguments: values
supplied without an option name, such as `src/Engine.php` in
`infection run src/Engine.php`.

Class, method, and absolute source line selectors let users select symbols
without knowing their file paths. However, a name can refer to either a path
fragment or a class: `infection run Differ` already selects files whose paths
contain `Differ`, but could also mean the class named `Differ`. These
interpretations can select different code. For example,
`src/Differ/Helper.php` matches the path fragment even if it contains no class
named `Differ`.

Users can express their intent explicitly with a trailing `/` (`Differ/`) for a
path or a trailing `\` (`Differ\`) for a namespace prefix. Bare names such as
`Differ` remain ambiguous, so the classifier needs rules for interpreting
them. These rules must preserve the meaning of readable paths, including paths
containing characters used by selectors. Classification must also work without
loading the user's classes.

## Decision

The classifier gives readable files and directories precedence over symbol
parsing, using the existing source-directory and test-path rules. It does not
autoload the user's classes.

When an argument does not resolve to a readable path, the classifier considers
selector syntax before applying the path-fragment rules. A bare PHP identifier
such as `Differ` supplies both a path-substring filter and a short-class-name
selector. These constraints intersect: only matching classes in matching files
can produce mutations.
When several symbol selectors are supplied, their matches form a union within
the same file constraint.

Users can explicitly select paths with a trailing slash (`Differ/`) or
namespace prefixes with a trailing backslash (`Differ\`). The latter selects
classes such as `Differ\ColorizedDiffer`, excluding the class named `Differ`.
A leading backslash makes a class name fully qualified, including a class in
the global namespace (`\Differ`). These forms remain subject to readable-path
precedence, and shell arguments containing backslashes should be quoted.

The selector syntax also supports a method name, an absolute source line number,
or both, separated by `::`: `Differ::diff`, `Differ::32`, and `Differ::diff::32`.
Malformed explicit selectors are rejected rather than silently interpreted as
paths.

## Consequences

Existing readable paths keep their meaning. Users can select symbols without
knowing their file paths or requiring an autoloader.

Unresolved bare names now apply both a file filter and a symbol filter.
For example, consider these files and the classes they declare:

```text
src/
    Differ/
        Differ.php           # App\Differ\Differ
        Helper.php           # App\Differ\Helper
    Other/
        ColorizedDiffer.php  # Differ\ColorizedDiffer
        Namespaced.php       # App\Other\Differ
        Global.php           # Differ
```

Previously, `infection run Differ` selected `Differ.php`, `Helper.php`, and
`ColorizedDiffer.php` because their paths contain `Differ`. The file filter
still selects those files, but the symbol filter limits mutation to classes
whose short name is `Differ`. In this example, only `App\Differ\Differ` in
`src/Differ/Differ.php` can produce mutations. The `Differ` classes in
`Namespaced.php` and `Global.php` remain excluded because their paths do not match.

Users can select a different scope explicitly:

- `infection run Differ/` selects both files under `src/Differ/` without a
  symbol filter.
- `infection run 'Differ\'` selects classes under that namespace prefix across
  all source files. In this example, it selects only `Differ\ColorizedDiffer`.
- `infection run '\Differ'` selects only the global class in `Global.php`.

The compact syntax introduces ambiguity and requires users to learn the markers.
A readable path can take precedence over an intended symbol selector;
a qualified symbol name normally resolves that conflict.

## Enforcement

`PositionalArgumentsClassifierTest` covers path precedence and the three
classification categories. Parser, matcher, and visitor tests cover selector
syntax and node eligibility. The `Source_Symbol_Selection` end-to-end scenario
checks mutation generation for bare names and the explicit markers.

## Alternatives considered

Requiring explicit symbol syntax for every selector, such as a separate option
or a mandatory marker, would preserve all bare path fragments and reduce
ambiguity, but would make common class and method selectors more verbose.
The chosen syntax supports short positional selectors while providing explicit
forms for each interpretation.

Giving symbols precedence over readable paths would make class selection less
dependent on the filesystem, but could reinterpret existing path arguments and
Windows paths. Preserving readable paths takes priority for compatibility.

Keeping bare names as paths only would preserve their previous behaviour, but
a bare class name would then behave differently from its method selector.
The chosen intersection keeps the file constraint while enabling class selection.

## Status

Accepted.
