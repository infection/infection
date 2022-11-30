<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node\NamedIdentifier;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender\ParentNodeAppender;
use _HumbugBoxb47773b41c19\PhpParser\Node\Identifier;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name\FullyQualified;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\String_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Function_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitor\NameResolver;
use function array_filter;
use function implode;
use function ltrim;
final class IdentifierResolver
{
    public function __construct(private readonly NameResolver $nameResolver)
    {
    }
    public function resolveIdentifier(Identifier $identifier) : Name
    {
        $resolvedName = $identifier->getAttribute('resolvedName');
        if (null !== $resolvedName) {
            return $resolvedName;
        }
        $parentNode = ParentNodeAppender::getParent($identifier);
        if ($parentNode instanceof Function_) {
            return $this->resolveFunctionIdentifier($identifier);
        }
        $name = NamedIdentifier::create($identifier);
        return $this->nameResolver->getNameContext()->getResolvedClassName($name);
    }
    public function resolveString(String_ $string) : Name
    {
        $name = new FullyQualified(ltrim($string->value, '\\'), $string->getAttributes());
        return $this->nameResolver->getNameContext()->getResolvedClassName($name);
    }
    private function resolveFunctionIdentifier(Identifier $identifier) : Name
    {
        $nameParts = array_filter([$this->nameResolver->getNameContext()->getNamespace(), $identifier->toString()]);
        return new FullyQualified(implode('\\', $nameParts), $identifier->getAttributes());
    }
}
