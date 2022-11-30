<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node;

class VarLikeIdentifier extends Identifier
{
    public function getType() : string
    {
        return 'VarLikeIdentifier';
    }
}
