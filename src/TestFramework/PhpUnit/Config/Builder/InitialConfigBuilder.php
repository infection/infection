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

namespace Infection\TestFramework\PhpUnit\Config\Builder;

use DOMDocument;
use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use Infection\TestFramework\SafeDOMXPath;
use function Safe\file_put_contents;
use function sprintf;
use function version_compare;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
class InitialConfigBuilder implements ConfigBuilder
{
    private readonly string $originalXmlConfigContent;

    /**
     * @param string[] $srcDirs
     * @param list<string> $filteredSourceFilesToMutate
     */
    public function __construct(
        private readonly string $tmpDir,
        string $originalXmlConfigContent,
        private readonly XmlConfigurationManipulator $configManipulator,
        private readonly XmlConfigurationVersionProvider $versionProvider,
        private readonly array $srcDirs,
        private readonly array $filteredSourceFilesToMutate,
    ) {
        Assert::notEmpty(
            $originalXmlConfigContent,
            'The original XML config content cannot be an empty string',
        );
        $this->originalXmlConfigContent = $originalXmlConfigContent;
    }

    public function build(string $version): string
    {
        $path = $this->buildPath();

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $success = @$dom->loadXML($this->originalXmlConfigContent);

        Assert::true($success);

        $xPath = new SafeDOMXPath($dom);

        $this->configManipulator->validate($path, $xPath);

        $this->addCoverageNodes($version, $xPath);
        $this->addRandomTestsOrderAttributesIfNotSet($version, $xPath);
        $this->configManipulator->addFailOnAttributesIfNotSet($version, $xPath);
        $this->configManipulator->replaceWithAbsolutePaths($xPath);
        $this->configManipulator->setStopOnFailureOrDefect($version, $xPath);
        $this->configManipulator->deactivateColours($xPath);
        $this->configManipulator->deactivateResultCaching($xPath);
        $this->configManipulator->deactivateStderrRedirection($xPath);
        $this->configManipulator->removeExistingLoggers($xPath);
        $this->configManipulator->removeExistingPrinters($xPath);

        file_put_contents($path, $dom->saveXML());

        return $path;
    }

    private function buildPath(): string
    {
        return $this->tmpDir . '/phpunitConfiguration.initial.infection.xml';
    }

    private function addCoverageNodes(string $version, SafeDOMXPath $xPath): void
    {
        if (version_compare($version, '10.1', '>=')) {
            $this->configManipulator->addOrUpdateSourceIncludeNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);

            return;
        }

        if (version_compare($version, '10', '>=')) {
            $this->configManipulator->addOrUpdateCoverageIncludeNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);

            return;
        }

        if (version_compare($version, '9.3', '<')) {
            $this->configManipulator->addOrUpdateLegacyCoverageWhitelistNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);

            return;
        }

        // For versions between 9.3 and 10.0, fallback to version provider
        if (version_compare($this->versionProvider->provide($xPath), '9.3', '>=')) {
            $this->configManipulator->addOrUpdateCoverageIncludeNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);

            return;
        }

        $this->configManipulator->addOrUpdateLegacyCoverageWhitelistNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);
    }

    private function addRandomTestsOrderAttributesIfNotSet(string $version, SafeDOMXPath $xPath): void
    {
        if (version_compare($version, '7.2', '<')) {
            return;
        }

        if ($this->addAttributeIfNotSet('executionOrder', 'random', $xPath)) {
            $this->addAttributeIfNotSet('resolveDependencies', 'true', $xPath);
        }
    }

    private function addAttributeIfNotSet(string $attribute, string $value, SafeDOMXPath $xPath): bool
    {
        $nodeList = $xPath->query(sprintf('/phpunit/@%s', $attribute));

        if ($nodeList->length === 0) {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute($attribute, $value);

            return true;
        }

        return false;
    }
}
