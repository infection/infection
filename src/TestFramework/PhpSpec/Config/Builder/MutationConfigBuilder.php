<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
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

namespace Infection\TestFramework\PhpSpec\Config\Builder;

use function array_key_exists;
use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\StreamWrapper\IncludeInterceptor;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Infection\TestFramework\PhpSpec\Config\MutationYamlConfiguration;
use ReflectionClass;
use function Safe\file_put_contents;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
class MutationConfigBuilder extends ConfigBuilder
{
    private $tempDirectory;
    private $originalYamlConfigPath;
    private $projectDir;

    public function __construct(string $tempDirectory, string $originalYamlConfigPath, string $projectDir)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalYamlConfigPath = $originalYamlConfigPath;
        $this->projectDir = $projectDir;
    }

    /**
     * @param CoverageLineData[] $coverageTests
     */
    public function build(
        array $coverageTests,
        string $mutantFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath
    ): string {
        $customAutoloadFilePath = sprintf(
            '%s/interceptor.phpspec.autoload.%s.infection.php',
            $this->tempDirectory,
            $mutationHash
        );

        $parsedYaml = Yaml::parseFile($this->originalYamlConfigPath);

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutationOriginalFilePath, $mutantFilePath, $parsedYaml));

        $yamlConfiguration = new MutationYamlConfiguration(
            $this->tempDirectory,
            $parsedYaml,
            $customAutoloadFilePath
        );

        $newYaml = $yamlConfiguration->getYaml();

        $path = $this->buildPath($mutationHash);

        file_put_contents($path, $newYaml);

        return $path;
    }

    private function createCustomAutoloadWithInterceptor(string $originalFilePath, string $mutantFilePath, array $parsedYaml): string
    {
        $originalBootstrap = $this->getOriginalBootstrapFilePath($parsedYaml);
        $autoloadPlaceholder = $originalBootstrap ? "require_once '{$originalBootstrap}';" : '';
        $interceptorPath = IncludeInterceptor::LOCATION;

        $customAutoload = <<<AUTOLOAD
<?php

%s
%s

AUTOLOAD;

        return sprintf(
            $customAutoload,
            $autoloadPlaceholder,
            $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutantFilePath)
        );
    }

    private function buildPath(string $mutationHash): string
    {
        $fileName = sprintf('phpspecConfiguration.%s.infection.yml', $mutationHash);

        return $this->tempDirectory . '/' . $fileName;
    }

    /**
     * @return string|null
     */
    private function getOriginalBootstrapFilePath(array $parsedYaml)
    {
        if (!array_key_exists('bootstrap', $parsedYaml)) {
            return null;
        }

        return sprintf('%s/%s', $this->projectDir, $parsedYaml['bootstrap']);
    }
}
