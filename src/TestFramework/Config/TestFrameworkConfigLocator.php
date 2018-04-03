<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Config;

use Infection\Finder\Exception\LocatorException;

final class TestFrameworkConfigLocator implements TestFrameworkConfigLocatorInterface
{
    const DEFAULT_EXTENSIONS = [
        'xml',
        'yml',
        'xml.dist',
        'yml.dist',
        'dist.xml',
        'dist.yml',
    ];

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
        $triedFiles = [];

        foreach (static::DEFAULT_EXTENSIONS as $extension) {
            $conf = sprintf('%s/%s.%s', $dir, $testFrameworkName, $extension);

            if (file_exists($conf)) {
                return realpath($conf);
            }

            $triedFiles[] = sprintf('%s.%s', $testFrameworkName, $extension);
        }

        throw LocatorException::multipleFilesDoNotExist(
            $dir,
            $triedFiles
        );
    }
}
