<?php

declare(strict_types=1);

namespace Infection\Mutator;

use Webmozart\Assert\Assert;

final class Classification
{
    /**
     * Sematic reductions - These are almost always guaranteed to be "good" mutations the user definitively wants to kill as they expose unused semantics. Most trivial exampe is `a; b` (where both perform a not dependent side effect) to: `b`. Simple line and branch coverage would in case side effect `b` is measured give a 100% covered result, where the mutation would expose `a` is not covered in case the mutation is alive. Note that semantic reductions come in various alternative forms, many of them are language / intermediate specific. The thing to IMO recall on these is: They will as they expose untested semantics almost never equivalents as: If the semantics are unneded, they should be removed, and if they are untested a test should be added. Another good canonical example for semantic reduction is `a(b)` -> `b`. And transitively all `n1(n2(x))` -> `n1(x)` / `n2(x)`. I assume these pass a type check. I suspect 80 - 90% of traditional mutations for a useful tool fall into this class.
     */
    public const SEMANTIC_REDUCTION = 'semanticReduction';

    /**
     * Orthogonal replacement - These are normally "good" mutations, but can lead to equivalents. A good example for a useful orthogonal replacement is `a < b` to `a > b`. It cannot be argued which one has less semantics, but it can be argued that an alive mutation for these show significant uncoverage that should be fixed.
     */
    public const ORTHOGONAL_REPLACEMENT = 'orthogonalReplacement';

    /**
     * Neutral mutations - These are useful to discover integration errors. The most trivial neutral mutation is `a` -> `a` (noop). Mutant uses this mutation to test if the insertion mechanism by itself already causes test failures in the environment (so a future semantic reduction may not be detectable as the insertion process itself has a bug causing test errors, not the mutation effect causing errors). I've seen horrible integraiton errors that lead to a high number of false "good coverage" without these tests. Other neutral mutations are normally language / convention specific. Example is that for many languages `a += b` op assign, should just be syntax sugar for `a = a + b`, where for others the verbose form should not be equivalent. Mutating to semantic equivalent forms is useful to test if structure laws are still obeyed by having the tests still green. Summary: Neutral mutations typically have a test green expectation and are useful to test mutation testing tool errors and language misuse.
     */
    public const NEUTRAL = 'neutral';

    /**
     * Semantic additions - These are usually not mutations that make sense to use in the traditional sense. Mutating `a` to `a; b` only makes sense if you have tests that can guarantee that "not more than specified" is implemented, and outside of systems with formal proofs (or mutation testings with reduction) usually tests cannot guarantee the absence of unspecified semantics. Nevertheless semantic additions can be really useful to add temporal instrumentation to code to gather more data to use to emit interesting mutations for the classes above. Example is that there is a private version of mutant that does "Runtime type detection" so I can use static type data gathered from runs under "type profiling operators" to emit type directed mutations. Summary I think semantic additions are most useful for tooling.
     */
    public const SEMANTIC_ADDITION = 'semanticAddition';

    public const ALL = [
        self::SEMANTIC_REDUCTION,
        self::ORTHOGONAL_REPLACEMENT,
        self::NEUTRAL,
        self::SEMANTIC_ADDITION,
    ];

    private function __construct()
    {
    }
}
