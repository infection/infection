<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Extensions;

use _HumbugBox9658796bb9f0\Infection\Mutator\AllowedFunctionsConfig;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorConfig;
final class BCMathConfig extends AllowedFunctionsConfig implements MutatorConfig
{
    private const KNOWN_FUNCTIONS = ['bcadd', 'bccomp', 'bcdiv', 'bcmod', 'bcmul', 'bcpow', 'bcsub', 'bcsqrt', 'bcpowmod'];
    public function __construct(array $settings)
    {
        parent::__construct($settings, self::KNOWN_FUNCTIONS);
    }
}
