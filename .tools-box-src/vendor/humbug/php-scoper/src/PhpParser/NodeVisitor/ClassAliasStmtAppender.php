<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node\ClassAliasFuncCall;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\IdentifierResolver;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\UnexpectedParsingScenario;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Class_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Expression;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Interface_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Namespace_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
use function array_reduce;
final class ClassAliasStmtAppender extends NodeVisitorAbstract
{
    public function __construct(private readonly IdentifierResolver $identifierResolver, private readonly SymbolsRegistry $symbolsRegistry)
    {
    }
    public function afterTraverse(array $nodes) : array
    {
        $newNodes = [];
        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                $node = $this->appendToNamespaceStmt($node);
            }
            $newNodes[] = $node;
        }
        return $newNodes;
    }
    private function appendToNamespaceStmt(Namespace_ $namespace) : Namespace_
    {
        $namespace->stmts = array_reduce($namespace->stmts, fn(array $stmts, Stmt $stmt) => $this->createNamespaceStmts($stmts, $stmt), []);
        return $namespace;
    }
    private function createNamespaceStmts(array $stmts, Stmt $stmt) : array
    {
        $stmts[] = $stmt;
        $isClassOrInterface = $stmt instanceof Class_ || $stmt instanceof Interface_;
        if (!$isClassOrInterface) {
            return $stmts;
        }
        $name = $stmt->name;
        if (null === $name) {
            throw UnexpectedParsingScenario::create();
        }
        $resolvedName = $this->identifierResolver->resolveIdentifier($name);
        if (!$resolvedName instanceof FullyQualified) {
            return $stmts;
        }
        $record = $this->symbolsRegistry->getRecordedClass((string) $resolvedName);
        if (null !== $record) {
            $stmts[] = self::createAliasStmt($record[0], $record[1], $stmt);
        }
        return $stmts;
    }
    private static function createAliasStmt(string $originalName, string $prefixedName, Node $stmt) : Expression
    {
        $call = new ClassAliasFuncCall(new FullyQualified($prefixedName), new FullyQualified($originalName), $stmt->getAttributes());
        $expression = new Expression($call, $stmt->getAttributes());
        ParentNodeAppender::setParent($call, $expression);
        return $expression;
    }
}
