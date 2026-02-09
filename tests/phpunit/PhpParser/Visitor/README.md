# Testing PhpParser Visitors

This document explains how to use the testing utilities provided in this directory to test PhpParser visitors.


## Overview

The `VisitorTestCase` base class and accompanying utilities provide a comprehensive framework for testing PhpParser node
visitors. These utilities help you:

- Parse PHP code into an Abstract Syntax Tree (AST).
- Apply visitors to the AST.
- Dump the AST in a readable, testable format.
- Track which nodes were visited by your visitor.
- Filter attributes to focus on what matters in your tests.


## Getting Started

### Extending VisitorTestCase

Create your test class by extending `VisitorTestCase`:

```php
<?php

namespace Infection\Tests\PhpParser\Visitor;

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
- **`$this->dumper`**: A NodeDumper for converting AST nodes into readable strings.


## Core Testing Pattern

The typical pattern for testing a visitor follows these steps:

### 1. Parse the Code

```php
$nodes = $this->parse($code);
```


### 2. Add IDs to Nodes (Recommended)

Add sequential IDs to all nodes. This helps:

- Identify nodes uniquely and deterministically in test output.
- Handle circular references in node attributes.
- Make test failures easier to debug.

```php
$this->addIdsToNodes($nodes);
```


### 3. Apply Your Visitor

Use a `NodeTraverser` to apply your visitor (and any dependencies) to the nodes:

```php
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\NodeTraverser;

(new NodeTraverser(
    new ParentConnectingVisitor(),              // If your visitor needs parent references
    new NameResolver(),                         // If your visitor needs resolved names
    new YourVisitor(),                          // Your visitor under test
    new MarkTraversedNodesAsVisitedVisitor(),   // Mark visited nodes
))->traverse($nodes);
```


### 4. Filter Attributes (Optional)

If your test only cares about specific node attributes, filter them:

```php
$this->keepOnlyDesiredAttributes(
    $nodes,
    'attributeName1',
    'attributeName2',
);
```

Note: The `visited` attribute is automatically preserved when using `keepOnlyDesiredAttributes()`.


### 5. Dump and Assert

Dump the AST and assert against the expected output:

```php
$actual = $this->dumper->dump($nodes);

$this->assertSame($expected, $actual);
```


## Complete Example

Here's a full example from `ReflectionVisitorTest`:

```php
public function test_it_annotates_nodes(
    string $code,
    ?array $desiredAttributes,
    string $expected,
): void {
    // 1. Parse the code
    $nodes = $this->parse($code);

    // 2. Add IDs for easier debugging; This is totally optional.
    //    If there are attributes with circular dependencies, the IDs
    //    allow handling them out the box. Otherwise, those attributes
    //    will have to be explicitly removed.
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

Key features:

- **Node types**: e.g., `Stmt_Namespace`, `Name`, `Identifier`
- **Properties**: e.g., `name`, `stmts`
- **Attributes**: e.g., `nodeId`, `parent`, `customAttribute`
- **References**: Nodes referenced in attributes show as `nodeId(N)` to avoid circular references
- **Skipped nodes**: Unvisited nodes show as `<skipped>` (when using `MarkTraversedNodesAsVisitedVisitor`)


## Testing Utilities Reference

### NodeDumper

The dumper is configured by default to:

- Dump only visited nodes (nodes without the `visited` attribute show as `<skipped>`).
- Exclude comments and position information.
- Dump "other" attributes (omit the native PhpParser ones like `startLine`, `endLine`, etc.).

You can customize dumping with additional parameters:

```php
$output = $this->dumper->dump(
    $nodes,
    $code,                   // Original code (needed for position dumping)
    dumpPositions: true,     // Include line/column information
    onlyVisitedNodes: false, // Show all nodes, not just visited ones
);
```

Or by overriding `VisitorTestCase::createDumper()` to configure the dumper directly.
