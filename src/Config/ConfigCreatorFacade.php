<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Config;

use Infection\Config\Validator as ConfigValidator;
use Infection\Finder\Exception\LocatorException;
use Infection\Finder\LocatorInterface;
use Infection\Json\JsonFile;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class ConfigCreatorFacade
{
    /**
     * @var ConfigValidator
     */
    private $configValidator;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(LocatorInterface $locator, Filesystem $filesystem)
    {
        $this->locator = $locator;
        $this->filesystem = $filesystem;

        $this->configValidator = new Validator();
    }

    public function createConfig(?string $customConfigPath): InfectionConfig
    {
        try {
            $infectionConfigFile = $this->locateConfigFile($customConfigPath);

            $content = (new JsonFile($infectionConfigFile))->decode();

            $configLocation = \pathinfo($infectionConfigFile, PATHINFO_DIRNAME);
        } catch (LocatorException $e) {
            // Generate an empty class to trigger `configure` command.
            $content = new \stdClass();

            $configLocation = getcwd();
        }

        // getcwd() may return false in rare circumstances
        \assert(\is_string($configLocation));

        $infectionConfig = new InfectionConfig($content, $this->filesystem, $configLocation);

        $this->configValidator->validate($infectionConfig);

        return $infectionConfig;
    }

    private function locateConfigFile(?string $customConfigPath): string
    {
        $configPaths = [];

        if ($customConfigPath) {
            $configPaths[] = $customConfigPath;
        }

        $configPaths = array_merge(
            $configPaths,
            InfectionConfig::POSSIBLE_CONFIG_FILE_NAMES
        );

        return $this->locator->locateOneOf($configPaths);
    }
}
