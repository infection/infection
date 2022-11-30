<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\Builder;

use DOMDocument;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\InitialConfigBuilder as ConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\XmlConfigurationVersionProvider;
use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
use function _HumbugBox9658796bb9f0\Safe\file_put_contents;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function version_compare;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class InitialConfigBuilder implements ConfigBuilder
{
    private string $originalXmlConfigContent;
    public function __construct(private string $tmpDir, string $originalXmlConfigContent, private XmlConfigurationManipulator $configManipulator, private XmlConfigurationVersionProvider $versionProvider, private array $srcDirs, private array $filteredSourceFilesToMutate)
    {
        Assert::notEmpty($originalXmlConfigContent, 'The original XML config content cannot be an empty string');
        $this->originalXmlConfigContent = $originalXmlConfigContent;
    }
    public function build(string $version) : string
    {
        $path = $this->buildPath();
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = \false;
        $dom->formatOutput = \true;
        $success = @$dom->loadXML($this->originalXmlConfigContent);
        Assert::true($success);
        $xPath = new SafeDOMXPath($dom);
        $this->configManipulator->validate($path, $xPath);
        $this->addCoverageNodes($version, $xPath);
        $this->addRandomTestsOrderAttributesIfNotSet($version, $xPath);
        $this->configManipulator->addFailOnAttributesIfNotSet($version, $xPath);
        $this->configManipulator->replaceWithAbsolutePaths($xPath);
        $this->configManipulator->setStopOnFailure($xPath);
        $this->configManipulator->deactivateColours($xPath);
        $this->configManipulator->deactivateResultCaching($xPath);
        $this->configManipulator->deactivateStderrRedirection($xPath);
        $this->configManipulator->removeExistingLoggers($xPath);
        $this->configManipulator->removeExistingPrinters($xPath);
        file_put_contents($path, $dom->saveXML());
        return $path;
    }
    private function buildPath() : string
    {
        return $this->tmpDir . '/phpunitConfiguration.initial.infection.xml';
    }
    private function addCoverageNodes(string $version, SafeDOMXPath $xPath) : void
    {
        if (version_compare($version, '10', '>=')) {
            $this->configManipulator->addOrUpdateCoverageIncludeNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);
            return;
        }
        if (version_compare($version, '9.3', '<')) {
            $this->configManipulator->addOrUpdateLegacyCoverageWhitelistNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);
            return;
        }
        if (version_compare($this->versionProvider->provide($xPath), '9.3', '>=')) {
            $this->configManipulator->addOrUpdateCoverageIncludeNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);
            return;
        }
        $this->configManipulator->addOrUpdateLegacyCoverageWhitelistNodes($xPath, $this->srcDirs, $this->filteredSourceFilesToMutate);
    }
    private function addRandomTestsOrderAttributesIfNotSet(string $version, SafeDOMXPath $xPath) : void
    {
        if (version_compare($version, '7.2', '<')) {
            return;
        }
        if ($this->addAttributeIfNotSet('executionOrder', 'random', $xPath)) {
            $this->addAttributeIfNotSet('resolveDependencies', 'true', $xPath);
        }
    }
    private function addAttributeIfNotSet(string $attribute, string $value, SafeDOMXPath $xPath) : bool
    {
        $nodeList = $xPath->query(sprintf('/phpunit/@%s', $attribute));
        if ($nodeList->length === 0) {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute($attribute, $value);
            return \true;
        }
        return \false;
    }
}
