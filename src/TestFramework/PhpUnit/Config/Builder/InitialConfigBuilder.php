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

use DOMElement;
use Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;
use Infection\TestFramework\Coverage\XMLLineCodeCoverage;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;

/**
 * @internal
 */
class InitialConfigBuilder implements ConfigBuilder
{
    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $originalXmlConfigContent;
    /**
     * @var XmlConfigurationHelper
     */
    private $xmlConfigurationHelper;

    /**
     * @var string
     */
    private $jUnitFilePath;

    /**
     * @var array
     */
    private $srcDirs = [];

    /**
     * @var bool
     */
    private $skipCoverage;

    public function __construct(
        string $tmpDir,
        string $originalXmlConfigContent,
        XmlConfigurationHelper $xmlConfigurationHelper,
        string $jUnitFilePath,
        array $srcDirs,
        bool $skipCoverage
    ) {
        $this->tmpDir = $tmpDir;
        $this->originalXmlConfigContent = $originalXmlConfigContent;
        $this->xmlConfigurationHelper = $xmlConfigurationHelper;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->srcDirs = $srcDirs;
        $this->skipCoverage = $skipCoverage;
    }

    public function build(string $version): string
    {
        $path = $this->buildPath();

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->originalXmlConfigContent);

        $xPath = new \DOMXPath($dom);

        $this->xmlConfigurationHelper->validate($dom, $xPath);

        $this->addCoverageFilterWhitelistIfDoesNotExist($dom, $xPath);
        $this->addRandomTestsOrderAttributesIfNotSet($version, $xPath);
        $this->xmlConfigurationHelper->replaceWithAbsolutePaths($xPath);
        $this->xmlConfigurationHelper->setStopOnFailure($xPath);
        $this->xmlConfigurationHelper->deactivateColours($xPath);
        $this->xmlConfigurationHelper->deactivateResultCaching($xPath);
        $this->xmlConfigurationHelper->removeExistingLoggers($dom, $xPath);
        $this->xmlConfigurationHelper->removeExistingPrinters($dom, $xPath);

        if (!$this->skipCoverage) {
            $this->addCodeCoverageLogger($dom, $xPath);
            $this->addJUnitLogger($dom, $xPath);
        }

        file_put_contents($path, $dom->saveXML());

        return $path;
    }

    private function buildPath(): string
    {
        return $this->tmpDir . '/phpunitConfiguration.initial.infection.xml';
    }

    private function addJUnitLogger(\DOMDocument $dom, \DOMXPath $xPath): void
    {
        $logging = $this->getOrCreateNode($dom, $xPath, 'logging');

        $junitLog = $dom->createElement('log');
        $junitLog->setAttribute('type', 'junit');
        $junitLog->setAttribute('target', $this->jUnitFilePath);

        $logging->appendChild($junitLog);
    }

    private function addCodeCoverageLogger(\DOMDocument $dom, \DOMXPath $xPath): void
    {
        $logging = $this->getOrCreateNode($dom, $xPath, 'logging');

        $coverageXmlLog = $dom->createElement('log');
        $coverageXmlLog->setAttribute('type', 'coverage-xml');
        $coverageXmlLog->setAttribute('target', $this->tmpDir . '/' . XMLLineCodeCoverage::PHP_UNIT_COVERAGE_DIR);

        $logging->appendChild($coverageXmlLog);
    }

    private function addCoverageFilterWhitelistIfDoesNotExist(\DOMDocument $dom, \DOMXPath $xPath): void
    {
        $filterNode = $this->getNode($xPath, 'filter');

        if (!$filterNode) {
            $filterNode = $this->createNode($dom, 'filter');

            $whiteListNode = $dom->createElement('whitelist');

            foreach ($this->srcDirs as $srcDir) {
                $directoryNode = $dom->createElement(
                    'directory',
                    $srcDir
                );

                $whiteListNode->appendChild($directoryNode);
            }

            $filterNode->appendChild($whiteListNode);
        }
    }

    private function getOrCreateNode(\DOMDocument $dom, \DOMXPath $xPath, string $nodeName): \DOMElement
    {
        $node = $this->getNode($xPath, $nodeName);

        if (!$node) {
            $node = $this->createNode($dom, $nodeName);
        }

        return $node;
    }

    private function getNode(\DOMXPath $xPath, string $nodeName)
    {
        $nodeList = $xPath->query(sprintf('/phpunit/%s', $nodeName));

        if ($nodeList->length) {
            return $nodeList->item(0);
        }

        return null;
    }

    private function createNode(\DOMDocument $dom, string $nodeName): \DOMElement
    {
        $node = $dom->createElement($nodeName);
        $document = $dom->documentElement;
        \assert($document instanceof DOMElement);
        $document->appendChild($node);

        return $node;
    }

    private function addRandomTestsOrderAttributesIfNotSet(string $version, \DOMXPath $xPath): void
    {
        if (!version_compare($version, '7.2', '>=')) {
            return;
        }

        if ($this->addAttributeIfNotSet('executionOrder', 'random', $xPath)) {
            $this->addAttributeIfNotSet('resolveDependencies', 'true', $xPath);
        }
    }

    private function addAttributeIfNotSet(string $attribute, string $value, \DOMXPath $xPath): bool
    {
        $nodeList = $xPath->query(sprintf('/phpunit/@%s', $attribute));

        if (!$nodeList->length) {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute($attribute, $value);

            return true;
        }

        return false;
    }
}
