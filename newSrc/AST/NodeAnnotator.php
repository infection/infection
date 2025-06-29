<?php

declare(strict_types=1);

namespace newSrc\AST;

use PhpParser\Node;

final class NodeAnnotator
{
    public static function annotate(Node $node, Annotation $annotation): void
    {
        $node->setAttribute($annotation->name, $annotation->value);
    }

    private function __construct()
    {
    }
}
