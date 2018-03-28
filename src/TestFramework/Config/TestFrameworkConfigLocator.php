<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Config;

use Infection\Finder\Exception\LocatorException;

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
        $triedFiles = [];

        foreach ($this->getDefaultExtensions() as $extension) {
            $conf = sprintf('%s/%s.%s', $dir, $testFrameworkName, $extension);

            if (file_exists($conf)) {
                return realpath($conf);
            }

            $triedFiles[] = sprintf('%s.%s', $testFrameworkName, $extension);
        }

        throw LocatorException::multipleFilesDoNoExist(
            $dir,
            $triedFiles
        );
    }

    private function getDefaultExtensions(): array
    {
        return [
            'xml',
            'yml',
            'xml.dist',
            'yml.dist',
            'dist.xml',
            'dist.yml',
        ];
    }
}
