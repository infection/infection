<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator;

class MutatorConfig
{
    /**
     * @var array
     */
    private $ignoreConfig;

    public function __construct(array $config)
    {
        $this->ignoreConfig = $config['ignore'] ?? [];
    }

    public function isIgnored(string $class, string $method): bool
    {
        if (in_array($class, $this->ignoreConfig)) {
            return true;
        }

        if (in_array($class . '::' . $method, $this->ignoreConfig)) {
            return true;
        }

        foreach ($this->ignoreConfig as $ignorePattern) {
            if (fnmatch($ignorePattern, $class, FNM_NOESCAPE) || fnmatch($ignorePattern, $class . '::' . $method, FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }
}
