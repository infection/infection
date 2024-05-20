<?php

declare(strict_types=1);

namespace App\Mutator;

use Infection\Mutator\Definition;
use Infection\Mutator\Mutator;
use PhpParser\Node;

class __Name__ implements Mutator
{
    public function canMutate(Node $node): bool
    {
        // TODO: update the logic to decide if this mutator can mutate $node
        return false;
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<TODO>
     */
    public function mutate(Node $node): iterable
    {
        // TODO: update the logic to return mutated nodes
        yield $node;
    }

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                TODO: add description of this mutator here
                TXT
            ,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            null,
            <<<'DIFF'
                - TODO: show the source code before mutation
                + TODO: show the source code after mutation
                DIFF,
        );
    }

    public function getName(): string
    {
        return self::class;
    }
}
