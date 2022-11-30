<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Use_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\UseUse;
use function count;
use function sprintf;
final class UseStmtName
{
    public function __construct(private readonly Name $name)
    {
    }
    public function contains(Name $resolvedName) : bool
    {
        return self::arrayStartsWith($resolvedName->parts, $this->name->parts);
    }
    private static function arrayStartsWith(array $array, array $start) : bool
    {
        $prefixLength = count($start);
        for ($index = 0; $index < $prefixLength; ++$index) {
            if (!isset($array[$index]) || $array[$index] !== $start[$index]) {
                return \false;
            }
        }
        return \true;
    }
    public function getUseStmtAliasAndType() : array
    {
        $use = self::getUseNode($this->name);
        $useParent = self::getUseParentNode($use);
        $alias = $use->alias;
        if (null !== $alias) {
            $alias = (string) $alias;
        }
        return [$alias, $useParent->type];
    }
    private static function getUseNode(Name $name) : UseUse
    {
        $use = ParentNodeAppender::getParent($name);
        if ($use instanceof UseUse) {
            return $use;
        }
        throw new UnexpectedParsingScenario(sprintf('Unexpected use statement name parent "%s"', $use::class));
    }
    private static function getUseParentNode(UseUse $use) : Use_
    {
        $useParent = ParentNodeAppender::getParent($use);
        if ($useParent instanceof Use_) {
            return $useParent;
        }
        throw new UnexpectedParsingScenario(sprintf('Unexpected UseUse parent "%s"', $useParent::class));
    }
}
