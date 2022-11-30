<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Extensions;

use _HumbugBox9658796bb9f0\Infection\Mutator\AllowedFunctionsConfig;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorConfig;
final class MBStringConfig extends AllowedFunctionsConfig implements MutatorConfig
{
    private const KNOWN_FUNCTIONS = ['mb_chr', 'mb_ord', 'mb_parse_str', 'mb_send_mail', 'mb_strcut', 'mb_stripos', 'mb_stristr', 'mb_strlen', 'mb_strpos', 'mb_strrchr', 'mb_strripos', 'mb_strrpos', 'mb_strstr', 'mb_strtolower', 'mb_strtoupper', 'mb_str_split', 'mb_substr_count', 'mb_substr', 'mb_convert_case'];
    public function __construct(array $settings)
    {
        parent::__construct($settings, self::KNOWN_FUNCTIONS);
    }
}
