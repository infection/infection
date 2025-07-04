<?php

declare(strict_types=1);

namespace newSrc\Mutagenesis\Strategy;

use newSrc\Mutation\NodeVisitor\MutationCollectorVisitor;
use PhpParser\Node;
use Random\Engine\Mt19937;
use Random\Randomizer;
use SplObjectStorage;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_slice;
use function iter\toArray;

/**
 * @phpstan-import-type MutationFactory from MutationCollectorVisitor
 */
final readonly class RandomSelectionStrategy implements Strategy
{
    /**
     * @param int $seed
     * @param positive-int $limit
     */
    public function __construct(
        private int $seed,
        private int $limit,
    ) {
    }

    public function apply(SplObjectStorage $potentialMutations): iterable
    {
        $selectedOffsets = $this->selectOffsets($potentialMutations);

        foreach ($selectedOffsets as $node) {
            $createMutation = $potentialMutations[$node];

            yield from $createMutation($node);
        }
    }

    /**
     * @template T keyof SplObjectStorage
     *
     * @param SplObjectStorage<Node, MutationFactory> $potentialMutations
     *
     * @return Node[]
     */
    private function selectOffsets(SplObjectStorage $potentialMutations): array
    {
        $offsets = toArray($potentialMutations);

        $engine = new Mt19937($this->seed);
        $randomizer = new Randomizer($engine);

        $keys = array_keys($offsets);
        $shuffledKeys = $randomizer->shuffleArray($keys);

        $selectedKeys = array_slice($shuffledKeys, 0, $this->limit);

        return array_intersect_key(
            $offsets,
            array_flip($selectedKeys),
        );
    }
}
