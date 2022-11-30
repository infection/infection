<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\Builder;

use _HumbugBoxb47773b41c19\PhpParser\Builder;
use _HumbugBoxb47773b41c19\PhpParser\BuilderHelpers;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt;
class TraitUse implements Builder
{
    protected $traits = [];
    protected $adaptations = [];
    public function __construct(...$traits)
    {
        foreach ($traits as $trait) {
            $this->and($trait);
        }
    }
    public function and($trait)
    {
        $this->traits[] = BuilderHelpers::normalizeName($trait);
        return $this;
    }
    public function with($adaptation)
    {
        $adaptation = BuilderHelpers::normalizeNode($adaptation);
        if (!$adaptation instanceof Stmt\TraitUseAdaptation) {
            throw new \LogicException('Adaptation must have type TraitUseAdaptation');
        }
        $this->adaptations[] = $adaptation;
        return $this;
    }
    public function getNode() : Node
    {
        return new Stmt\TraitUse($this->traits, $this->adaptations);
    }
}
