<?php

declare(strict_types=1);

namespace newSrc\Mutagenesis\Strategy;

use SplObjectStorage;

final class SelectAllMutationsStrategy implements Strategy
{
    public function apply(SplObjectStorage $potentialMutations): iterable
    {
        foreach ($potentialMutations as $node) {
            $createMutation = $potentialMutations[$node];

            yield from $createMutation($node);
        }
    }
}
