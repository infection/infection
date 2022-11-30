<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node;

use _HumbugBoxb47773b41c19\PhpParser\Node\Identifier;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
final class NamedIdentifier extends Name
{
    private Identifier $originalNode;
    public static function create(Identifier $node) : self
    {
        $instance = new self($node->name, $node->getAttributes());
        $instance->originalNode = $node;
        return $instance;
    }
    public function getOriginalNode() : Identifier
    {
        return $this->originalNode;
    }
}
