<?php

declare(strict_types=1);

namespace newSrc\AST\NodeVisitor;

use PhpParser\NodeVisitor\NameResolver;

final class NameResolverFactory
{
    private function __construct()
    {
    }

    public static function create(): NameResolver
    {
        return new NameResolver(
            null,
            [
                'preserveOriginalNames' => true,
                'replaceNodes' => false,
            ],
        );
    }
}
