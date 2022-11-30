<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Node;

class VarLikeIdentifier extends Identifier
{
    public function getType() : string
    {
        return 'VarLikeIdentifier';
    }
}
