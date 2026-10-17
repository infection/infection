# Disambiguate positional source selectors

## Context

Infection accepts source paths and test paths as positional arguments: values
supplied without an option name, such as `src/Engine.php` in
`infection run src/Engine.php`.

Class, method, and absolute source line selectors let users select symbols
without knowing their file paths. However, a name can identify either a path
fragment or a class: `infection run Differ` already selects files whose paths
contain `Differ`, but could also mean the class named `Differ`. These
interpretations can select different code. For example,
`src/Differ/Helper.php` matches the path fragment even if it contains no class
named `Differ`.

Users can express their intent explicitly with a trailing `/` (`Differ/`) for a
path or a trailing `\` (`Differ\`) for a namespace prefix. Bare names such as
`Differ` remain ambiguous, so the classifier still needs rules for interpreting
them. These rules must preserve the meaning of readable paths, including paths
containing characters used by selectors. Classification must also work without
loading the user's classes.

## Decision

Classification gives readable files and directories precedence over symbol
parsing, using the existing source-directory and test-path rules. It does not
autoload the user's classes.

When an argument does not resolve to a readable path, the classifier considers
selector syntax before applying the path-fragment rules. A bare PHP identifier
such as `Differ` supplies both a path-substring filter and a short-class-name
selector. These constraints intersect, so only matching classes in matching
files can produce mutations.
When several symbol selectors are supplied, their matches form a union subject
to that file constraint.

Users can select paths explicitly with a trailing slash (`Differ/`) or a
namespace prefix with a trailing backslash (`Differ\`). The latter selects
classes such as `Differ\ColorizedDiffer`, excluding the class named `Differ`.
A leading backslash makes a class name fully qualified, including a class in
the global namespace (`\Differ`). These forms remain subject to readable-path
precedence, and shell arguments containing backslashes should be quoted.

The selector syntax also supports a method, an absolute source line, or both,
separated by `::`: `Differ::diff`, `Differ::32`, and `Differ::diff::32`.
Malformed explicit selectors are rejected rather than silently interpreted as
paths.

## Consequences

Existing readable paths keep their meaning. Users can select symbols without
knowing their file paths or requiring an autoloader.

Unresolved bare names change behaviour: `Differ` previously selected every file
whose path contained that text. It now also requires a class named `Differ`.
For example, `src/Differ/Helper.php` is excluded even though its path matches.
Users can use `Differ/` for a directory fragment, `Differ\` to select classes
under that namespace prefix across all source files, and `\Differ` to select
only the class in the global namespace.

The compact syntax introduces ambiguity and requires users to learn the markers.
A readable path can take precedence over a name the user intended as a selector;
a qualified symbol name normally resolves that conflict.

## Enforcement

`PositionalArgumentsClassifierTest` covers path precedence and the three
classification categories. Parser, matcher, and visitor tests cover selector
syntax and node eligibility. The `Source_Symbol_Selection` end-to-end scenario
checks mutation generation for bare names and the explicit markers.

## Alternatives considered

Requiring explicit symbol syntax for every selector, such as a separate option
or a mandatory marker, would preserve all bare path fragments and reduce
ambiguity, but would make common class and method selection more verbose.
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
