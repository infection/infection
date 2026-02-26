<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Mutator;

use Infection\Mutator\Definition;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use PhpParser\Node;

final class CustomNameMutator implements Mutator
{
    public static function getDefinition(): Definition
    {
        return new Definition('Custom Name Mutator Description', MutatorCategory::ORTHOGONAL_REPLACEMENT, null, 'diff');
    }

    public function getName(): string
    {
        return 'CustomNameMutator';
    }

    public function canMutate(Node $node): bool
    {
        return false;
    }

    public function mutate(Node $node): iterable
    {
        yield $node;
    }
}
