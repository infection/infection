<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
final class MutatorCategory
{
    use CannotBeInstantiated;
    public const SEMANTIC_REDUCTION = 'semanticReduction';
    public const SEMANTIC_ADDITION = 'semanticAddition';
    public const ORTHOGONAL_REPLACEMENT = 'orthogonalReplacement';
    public const ALL = [self::SEMANTIC_REDUCTION, self::SEMANTIC_ADDITION, self::ORTHOGONAL_REPLACEMENT];
}
