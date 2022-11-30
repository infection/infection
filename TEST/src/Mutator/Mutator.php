<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@template
*/
interface Mutator
{
    public static function getDefinition() : ?Definition;
    public function getName() : string;
    public function canMutate(Node $node) : bool;
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable;
}
