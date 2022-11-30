<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator;

use function array_fill_keys;
use function array_keys;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
abstract class AllowedFunctionsConfig
{
    private array $allowedFunctions;
    public function __construct(array $settings, array $knownFunctions)
    {
        $filteredSettings = array_fill_keys($knownFunctions, \true);
        foreach ($settings as $functionName => $enabled) {
            Assert::boolean($enabled, sprintf('Expected the value for "%s" to be a boolean. Got "%%s" instead', $functionName));
            Assert::oneOf($functionName, $knownFunctions);
            if (!$enabled) {
                unset($filteredSettings[$functionName]);
            }
        }
        $this->allowedFunctions = array_keys($filteredSettings);
    }
    public final function getAllowedFunctions() : array
    {
        return $this->allowedFunctions;
    }
}
