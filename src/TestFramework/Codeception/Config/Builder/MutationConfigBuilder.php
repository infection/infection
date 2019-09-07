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

namespace Infection\TestFramework\Codeception\Config\Builder;

use Infection\Mutant\MutantInterface;
use Infection\TestFramework\Codeception\Config\MutationYamlConfiguration;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class MutationConfigBuilder extends ConfigBuilder
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var array
     */
    private $originalConfigContentParsed;

    public function __construct(Filesystem $filesystem, string $tmpDir, string $projectDir, array $originalConfigContentParsed)
    {
        $this->tmpDir = $tmpDir;
        $this->projectDir = $projectDir;
        $this->originalConfigContentParsed = $originalConfigContentParsed;
        $this->filesystem = $filesystem;
    }

    public function build(MutantInterface $mutant): string
    {
        $mutationHash = $mutant->getMutation()->getHash();

        $interceptorFilePath = sprintf(
            '%s/interceptor.codeception.%s.php',
            $this->tmpDir,
            $mutationHash
        );

        file_put_contents($interceptorFilePath, $this->createCustomBootstrapWithInterceptor($mutant));

        $yamlConfiguration = new MutationYamlConfiguration(
            $this->tmpDir,
            $this->projectDir,
            $this->originalConfigContentParsed,
            $mutationHash,
            $interceptorFilePath
        );

        $newYaml = $yamlConfiguration->getYaml();

        $path = $this->buildPath($mutant);

        file_put_contents($path, $newYaml);

        return $path;
    }

    private function createCustomBootstrapWithInterceptor(MutantInterface $mutant): string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();

        $originalBootstrap = $this->getOriginalBootstrapFilePath();
        $bootstrapPlaceholder = $originalBootstrap ? "require_once '{$originalBootstrap}';" : '';

        $interceptorPath = \dirname(__DIR__, 4) . '/StreamWrapper/IncludeInterceptor.php';

        $customBootstrap = <<<AUTOLOAD
<?php

%s
%s

AUTOLOAD;

        return sprintf(
            $customBootstrap,
            $bootstrapPlaceholder,
            $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutatedFilePath)
        );
    }

    private function buildPath(MutantInterface $mutant): string
    {
        $fileName = sprintf('codeceptionConfiguration.%s.infection.yaml', $mutant->getMutation()->getHash());

        return $this->tmpDir . '/' . $fileName;
    }

    private function getOriginalBootstrapFilePath(): ?string
    {
        if (!\array_key_exists('bootstrap', $this->originalConfigContentParsed)) {
            return null;
        }

        if ($this->filesystem->isAbsolutePath($this->originalConfigContentParsed['bootstrap'])) {
            return $this->originalConfigContentParsed['bootstrap'];
        }

        return sprintf(
            '%s/%s/%s',
            $this->projectDir,
            $this->originalConfigContentParsed['paths']['tests'] ?? 'tests',
            $this->originalConfigContentParsed['bootstrap']
        );
    }
}
