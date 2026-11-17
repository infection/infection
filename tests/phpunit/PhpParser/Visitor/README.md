# Testing PhpParser Visitors

This guide explains how to test PhpParser visitors using the utilities in this directory.

## Overview

The `VisitorTestCase` base class and its supporting utilities let you:

- Parse PHP code into an Abstract Syntax Tree (AST).
- Apply visitors to the AST.
- Dump the AST in a readable, testable format.
- Track which nodes were visited.
- Filter attributes to show only those relevant to your test.

## Covering Visitor Behaviour and Integration

Use individual visitor tests to cover the visitor's behaviour in detail, including edge cases. When
changing node skipping, enrichment or visitor behaviour, normally add or extend a case in
[`EnrichmentTraverseIntegrationTest`](EnrichmentTraverse/EnrichmentTraverseIntegrationTest.php) too.
Cover at least the most relevant or critical scenarios there, and keep exhaustive coverage in the
individual visitor tests.

The integration test runs the production enrichment visitor stack followed by the mutation
traversal, then checks the AST as the mutation visitor sees it. It verifies that visitors work
together in the registered order and use shared attributes correctly. Individual visitor tests can
pass even when these interactions produce an incorrect AST.

## Generating and Comparing AST Dumps

Use `debug:dump-ast` to generate an AST dump in the same format as
`EnrichmentTraverseIntegrationTest`. It runs the same production enrichment and mutation traversers
and assigns node IDs. From the repository root, run:

```bash
./bin/infection debug:dump-ast path/to/fixture.php \
    --show-attributes \
    --no-ansi
```

`--show-attributes` includes enrichment attributes and node references in the output.

For scenarios involving changed lines, use the same inclusive line ranges as the integration test:

```bash
./bin/infection debug:dump-ast path/to/fixture.php \
    --show-attributes \
    --no-ansi \
    --changed-lines-ranges='10:12,24:30'
```

This includes line numbers in the dump, as in integration cases with changed line ranges. Use
`--configuration=path/to/infection.json5` to select a configuration. The command uses an empty
coverage trace and includes uncovered code; bear this in mind when comparing scenarios that depend
on coverage.

## Setting Up an Individual Visitor Test

### Extending VisitorTestCase

Create your test class by extending `VisitorTestCase`:

```php
<?php

namespace Infection\Tests\PhpParser\Visitor;

use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use YourNamespace\YourVisitor;

#[CoversClass(YourVisitor::class)]
final class YourVisitorTest extends VisitorTestCase
{
    #[DataProvider('visitorProvider')]
    public function test_it_modifies_nodes_correctly(
        string $code,
        string $expected,
    ): void {
        // Test implementation here
    }
}
```

### Available Properties

The base class provides two protected properties:

- **`$this->parser`**: A PHP parser for converting code strings into AST nodes.
- **`$this->dumper`**: A `NodeDumper` for converting AST nodes into readable strings.

## Core Testing Pattern

To test a visitor, follow these steps:

### 1. Parse the Code

```php
$nodes = $this->parse($code);
```

### 2. Add IDs to Nodes (Recommended)

Assign sequential IDs to all nodes to:

- Identify nodes uniquely and deterministically in test output.
- Handle circular references in node attributes.
- Make test failures easier to debug.

```php
$this->addIdsToNodes($nodes);
```

### 3. Apply Your Visitor

Use a `NodeTraverser` to apply your visitor and any dependencies to the nodes:

```php
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\NodeTraverser;

(new NodeTraverser(
    new ParentConnectingVisitor(),              // If your visitor needs parent references
    new NameResolver(),                         // If your visitor needs resolved names
    new YourVisitor(),                          // Your visitor under test
    new MarkTraversedNodesAsVisitedVisitor(),   // Mark visited nodes
))->traverse($nodes);
```

### 4. Filter Attributes (Optional)

To focus on specific node attributes, keep only those attributes:

```php
$this->keepOnlyDesiredAttributes(
    $nodes,
    'attributeName1',
    'attributeName2',
);
```

`keepOnlyDesiredAttributes()` always preserves the `visited` attribute.

### 5. Dump and Assert

Dump the AST and compare it with the expected output:

```php
$actual = $this->dumper->dump($nodes);

$this->assertSame($expected, $actual);
```

## Complete Example

This example from `ReflectionVisitorTest` brings the steps together:

```php
public function test_it_annotates_nodes(
    string $code,
    ?array $desiredAttributes,
    string $expected,
): void {
    // 1. Parse the code
    $nodes = $this->parse($code);

    // 2. Optionally add IDs for easier debugging. IDs let the dumper handle
    //    circular references in attributes. Without IDs, those attributes
    //    must be removed explicitly.
    $this->addIdsToNodes($nodes);

    // 3. Apply visitors
    (new NodeTraverser(
        new ParentConnectingVisitor(),
        new NameResolver(),
        new ReflectionVisitor(),
        new MarkTraversedNodesAsVisitedVisitor(),
    ))->traverse($nodes);

    // 4. Optionally filter attributes
    if ($desiredAttributes !== null) {
        $this->keepOnlyDesiredAttributes(
            $nodes,
            ...$desiredAttributes,
        );
    }

    // 5. Dump and assert
    $actual = $this->dumper->dump($nodes);

    $this->assertSame($expected, $actual);
}
```

## Understanding the Dumped Output

The `NodeDumper` produces output like this:

```
array(
    0: Stmt_Namespace(
        name: Name(
            nodeId: 1
            parent: nodeId(0)
        )
        stmts: array(
            0: Stmt_Class(
                name: Identifier(
                    nodeId: 3
                    parent: nodeId(2)
                )
                nodeId: 2
                parent: nodeId(0)
                customAttribute: someValue
            )
        )
        kind: 1
        nodeId: 0
    )
)
```

The dump shows:

- **Node types**: e.g., `Stmt_Namespace`, `Name`, `Identifier`
- **Properties**: e.g., `name`, `stmts`
- **Attributes**: e.g., `nodeId`, `parent`, `customAttribute`
- **References**: Nodes referenced in attributes show as `nodeId(N)` to avoid circular references
- **Skipped nodes**: Unvisited nodes show as `<skipped>` (when using
  `MarkTraversedNodesAsVisitedVisitor`)

## Testing Utilities Reference

### NodeDumper

The dumper is configured by default to:

- Dump only visited nodes (nodes without the `visited` attribute show as `<skipped>`).
- Exclude comments and position information.
- Dump "other" attributes, omitting native PhpParser attributes such as `startLine` and `endLine`.

You can customise the dump with additional parameters:

```php
$output = $this->dumper->dump(
    $nodes,
    $code,                   // Original code (needed for position dumping)
    dumpPositions: true,     // Include line/column information
    onlyVisitedNodes: false, // Show all nodes, not just visited ones
);
```

You can also override `VisitorTestCase::createDumper()` to configure the dumper directly.
