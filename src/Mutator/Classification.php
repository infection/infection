<?php

declare(strict_types=1);

namespace Infection\Mutator;

final class Classification
{
    /**
     * Semantic reductions exposes unused semantics. For example:
     *
     * ```php
     * $x = $a + $b;
     *
     * // to
     *
     * $x = $a;
     * ```
     *
     * If the semantics are unneeded, they should be removed. Otherwise they should be tested.
     */
    public const SEMANTIC_REDUCTION = 'semanticReduction';

    /**
     * An example of orthogonal replacement is:
     *
     * ```php
     * $a > $b;
     *
     * // to
     *
     * $a < $b;
     * ```
     *
     * Neither form has less semantics than the other. It is however a mutation that shows a lack
     * of coverage.
     */
    public const ORTHOGONAL_REPLACEMENT = 'orthogonalReplacement';

    /**
     * Neutral mutations aim at discovering integration errors. Example:
     *
     * ```
     * $a = $a + $b;
     *
     * // to
     *
     * $a += $b;
     * ```
     *
     * Mutating to a semantic equivalent form is useful to test if the structure laws are still
     * obeyed by having the tests still green. In other words it serves to test the mutation testing
     * tool errors and language misuse.
     */
    public const NEUTRAL = 'neutral';

    // Also known but unused for now: semantic addition

    public const ALL = [
        self::SEMANTIC_REDUCTION,
        self::ORTHOGONAL_REPLACEMENT,
        self::NEUTRAL,
    ];

    private function __construct()
    {
    }
}
