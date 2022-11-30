<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Environment;

use function array_key_exists;
use function array_slice;
use function is_string;
final class StrykerApiKeyResolver
{
    public function resolve(array $environment) : string
    {
        $names = ['INFECTION_BADGE_API_KEY', 'INFECTION_DASHBOARD_API_KEY', 'STRYKER_DASHBOARD_API_KEY'];
        foreach ($names as $name) {
            if (!array_key_exists($name, $environment) || !is_string($environment[$name])) {
                continue;
            }
            return $environment[$name];
        }
        throw CouldNotResolveStrykerApiKey::from(...array_slice($names, 1));
    }
}
