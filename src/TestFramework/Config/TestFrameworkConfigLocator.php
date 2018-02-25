<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Config;

class TestFrameworkConfigLocator
{
    /**
     * @var string
     */
    private $configDir;

    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
    }

    public function locate(string $testFrameworkName, string $customDir = null): string
    {
        $dir = $customDir ?: $this->configDir;

        foreach (['xml', 'yml'] as $extension) {
            $conf = sprintf('%s/%s.%s', $dir, $testFrameworkName, $extension);

            if (file_exists($conf)) {
                return realpath($conf);
            }

            if (file_exists($conf . '.dist')) {
                return realpath($conf . '.dist');
            }
        }

        throw new \RuntimeException(sprintf('Unable to locate %s.(xml|yml)(.dist) file.', $testFrameworkName));
    }
}
