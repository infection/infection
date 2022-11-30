<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver\IdentifierResolver;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Class_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Interface_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class IdentifierNameAppender extends NodeVisitorAbstract
{
    public function __construct(private readonly IdentifierResolver $identifierResolver)
    {
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!($node instanceof Class_ || $node instanceof Interface_)) {
            return null;
        }
        $name = $node->name;
        if (null === $name) {
            return null;
        }
        $resolvedName = $this->identifierResolver->resolveIdentifier($name);
        $name->setAttribute('resolvedName', $resolvedName);
        return null;
    }
}
