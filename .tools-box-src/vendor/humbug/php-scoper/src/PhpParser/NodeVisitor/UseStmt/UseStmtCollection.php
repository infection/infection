<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt;

use ArrayIterator;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node\NamedIdentifier;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\OriginalNameResolver;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\UnexpectedParsingScenario;
use IteratorAggregate;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\ConstFetch;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\FuncCall;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\ClassLike;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Function_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Use_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\UseUse;
use Traversable;
use function array_key_exists;
use function count;
use function implode;
use function strtolower;
final class UseStmtCollection implements IteratorAggregate
{
    private array $hashes = [];
    private array $nodes = [null => []];
    public function add(?Name $namespaceName, Use_ $use) : void
    {
        $this->nodes[(string) $namespaceName][] = $use;
    }
    public function findStatementForNode(?Name $namespaceName, Name $node) : ?Name
    {
        $name = self::getName($node);
        $parentNode = ParentNodeAppender::findParent($node);
        if ($parentNode instanceof ClassLike && $node instanceof NamedIdentifier && $node->getOriginalNode() === $parentNode->name) {
            throw UnexpectedParsingScenario::create();
        }
        $isFunctionName = self::isFunctionName($node, $parentNode);
        $isConstantName = self::isConstantName($node, $parentNode);
        $hash = implode(':', [$namespaceName ? $namespaceName->toString() : '', $name, $isFunctionName ? 'func' : '', $isConstantName ? 'const' : '']);
        if (array_key_exists($hash, $this->hashes)) {
            return $this->hashes[$hash];
        }
        return $this->hashes[$hash] = $this->find($this->nodes[(string) $namespaceName] ?? [], $isFunctionName, $isConstantName, $name);
    }
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->nodes);
    }
    private static function getName(Name $node) : string
    {
        return self::getNameFirstPart(OriginalNameResolver::getOriginalName($node));
    }
    private static function getNameFirstPart(Name $node) : string
    {
        return strtolower($node->getFirst());
    }
    private function find(array $useStatements, bool $isFunctionName, bool $isConstantName, string $name) : ?Name
    {
        foreach ($useStatements as $use_) {
            foreach ($use_->uses as $useStatement) {
                if (!$useStatement instanceof UseUse) {
                    continue;
                }
                $type = Use_::TYPE_UNKNOWN !== $use_->type ? $use_->type : $useStatement->type;
                if ($name !== $useStatement->getAlias()->toLowerString()) {
                    continue;
                }
                if ($isFunctionName) {
                    if (Use_::TYPE_FUNCTION === $type) {
                        return UseStmtManipulator::getOriginalName($useStatement);
                    }
                    continue;
                }
                if ($isConstantName) {
                    if (Use_::TYPE_CONSTANT === $type) {
                        return UseStmtManipulator::getOriginalName($useStatement);
                    }
                    continue;
                }
                if (Use_::TYPE_NORMAL === $type) {
                    return UseStmtManipulator::getOriginalName($useStatement);
                }
            }
        }
        return null;
    }
    private static function isFunctionName(Name $node, ?Node $parentNode) : bool
    {
        if (null === $parentNode) {
            throw UnexpectedParsingScenario::create();
        }
        if ($parentNode instanceof FuncCall) {
            return self::isFuncCallFunctionName($node);
        }
        if (!$parentNode instanceof Function_) {
            return \false;
        }
        return $node instanceof NamedIdentifier && $node->getOriginalNode() === $parentNode->name;
    }
    private static function isFuncCallFunctionName(Name $name) : bool
    {
        if ($name instanceof FullyQualified) {
            $name = OriginalNameResolver::getOriginalName($name);
        }
        return 1 === count($name->parts);
    }
    private static function isConstantName(Name $name, ?Node $parentNode) : bool
    {
        if (!$parentNode instanceof ConstFetch) {
            return \false;
        }
        if ($name instanceof FullyQualified) {
            $name = OriginalNameResolver::getOriginalName($name);
        }
        return 1 === count($name->parts);
    }
}
