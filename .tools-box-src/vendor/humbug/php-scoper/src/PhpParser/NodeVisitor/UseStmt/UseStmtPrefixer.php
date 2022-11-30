<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\UnexpectedParsingScenario;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\EnrichedReflector;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Use_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\UseUse;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class UseStmtPrefixer extends NodeVisitorAbstract
{
    public function __construct(private readonly string $prefix, private readonly EnrichedReflector $enrichedReflector)
    {
    }
    public function enterNode(Node $node) : Node
    {
        if ($node instanceof UseUse && $this->shouldPrefixUseStmt($node)) {
            self::prefixStmt($node, $this->prefix);
        }
        return $node;
    }
    private function shouldPrefixUseStmt(UseUse $use) : bool
    {
        $useType = self::findUseType($use);
        $nameString = $use->name->toString();
        $alreadyPrefixed = $this->prefix === $use->name->getFirst();
        if ($alreadyPrefixed) {
            return \false;
        }
        if ($this->enrichedReflector->belongsToExcludedNamespace($nameString)) {
            return \false;
        }
        if (Use_::TYPE_FUNCTION === $useType) {
            return !$this->enrichedReflector->isFunctionInternal($nameString);
        }
        if (Use_::TYPE_CONSTANT === $useType) {
            return !$this->enrichedReflector->isExposedConstant($nameString);
        }
        return Use_::TYPE_NORMAL !== $useType || !$this->enrichedReflector->isClassInternal($nameString);
    }
    private static function prefixStmt(UseUse $use, string $prefix) : void
    {
        $previousName = $use->name;
        $prefixedName = Name::concat($prefix, $use->name, $use->name->getAttributes());
        if (null === $prefixedName) {
            throw UnexpectedParsingScenario::create();
        }
        ParentNodeAppender::setParent($previousName, $use);
        UseStmtManipulator::setOriginalName($use, $previousName);
        $use->name = $prefixedName;
    }
    private static function findUseType(UseUse $use) : int
    {
        if (Use_::TYPE_UNKNOWN === $use->type) {
            $parentNode = ParentNodeAppender::getParent($use);
            return $parentNode->type;
        }
        return $use->type;
    }
}
