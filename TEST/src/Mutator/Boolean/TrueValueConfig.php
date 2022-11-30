<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Boolean;

use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorConfig;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class TrueValueConfig implements MutatorConfig
{
    private const KNOWN_FUNCTIONS = ['array_search', 'in_array'];
    private array $allowedFunctions = [];
    public function __construct(array $settings)
    {
        foreach ($settings as $functionName => $enabled) {
            Assert::boolean($enabled, sprintf('Expected the value for "%s" to be a boolean. Got "%%s" instead', $functionName));
            Assert::oneOf($functionName, self::KNOWN_FUNCTIONS);
            if ($enabled) {
                $this->allowedFunctions[] = $functionName;
            }
        }
    }
    public function getAllowedFunctions() : array
    {
        return $this->allowedFunctions;
    }
}
