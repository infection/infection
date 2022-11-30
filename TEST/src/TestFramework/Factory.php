<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use function array_filter;
use function array_map;
use function implode;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapterFactory;
use _HumbugBox9658796bb9f0\Infection\Configuration\Configuration;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\TestFrameworkFinder;
use _HumbugBox9658796bb9f0\Infection\FileSystem\SourceFileFilter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Adapter\PestAdapterFactory;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapterFactory;
use InvalidArgumentException;
use function is_a;
use function iterator_to_array;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use SplFileInfo;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class Factory
{
    public function __construct(private string $tmpDir, private string $projectDir, private TestFrameworkConfigLocatorInterface $configLocator, private TestFrameworkFinder $testFrameworkFinder, private string $jUnitFilePath, private Configuration $infectionConfig, private SourceFileFilter $sourceFileFilter, private array $installedExtensions)
    {
    }
    public function create(string $adapterName, bool $skipCoverage) : TestFrameworkAdapter
    {
        $filteredSourceFilesToMutate = $this->getFilteredSourceFilesToMutate();
        if ($adapterName === TestFrameworkTypes::PHPUNIT) {
            $phpUnitConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);
            return PhpUnitAdapterFactory::create($this->testFrameworkFinder->find(TestFrameworkTypes::PHPUNIT, (string) $this->infectionConfig->getPhpUnit()->getCustomPath()), $this->tmpDir, $phpUnitConfigPath, (string) $this->infectionConfig->getPhpUnit()->getConfigDir(), $this->jUnitFilePath, $this->projectDir, $this->infectionConfig->getSourceDirectories(), $skipCoverage, $this->infectionConfig->getExecuteOnlyCoveringTestCases(), $filteredSourceFilesToMutate);
        }
        if ($adapterName === TestFrameworkTypes::PEST) {
            $pestConfigPath = $this->configLocator->locate(TestFrameworkTypes::PHPUNIT);
            return PestAdapterFactory::create($this->testFrameworkFinder->find(TestFrameworkTypes::PEST, (string) $this->infectionConfig->getPhpUnit()->getCustomPath()), $this->tmpDir, $pestConfigPath, (string) $this->infectionConfig->getPhpUnit()->getConfigDir(), $this->jUnitFilePath, $this->projectDir, $this->infectionConfig->getSourceDirectories(), $skipCoverage, $this->infectionConfig->getExecuteOnlyCoveringTestCases(), $filteredSourceFilesToMutate);
        }
        $availableTestFrameworks = [TestFrameworkTypes::PHPUNIT, TestFrameworkTypes::PEST];
        foreach ($this->installedExtensions as $installedExtension) {
            $factory = $installedExtension['extra']['class'];
            Assert::classExists($factory);
            if (!is_a($factory, TestFrameworkAdapterFactory::class, \true)) {
                continue;
            }
            $availableTestFrameworks[] = $factory::getAdapterName();
            if ($adapterName === $factory::getAdapterName()) {
                return $factory::create($this->testFrameworkFinder->find($factory::getExecutableName()), $this->tmpDir, $this->configLocator->locate($factory::getAdapterName()), null, $this->jUnitFilePath, $this->projectDir, $this->infectionConfig->getSourceDirectories(), $skipCoverage);
            }
        }
        throw new InvalidArgumentException(sprintf('Invalid name of test framework "%s". Available names are: %s', $adapterName, implode(', ', $availableTestFrameworks)));
    }
    private function getFilteredSourceFilesToMutate() : array
    {
        if ($this->sourceFileFilter->getFilters() === []) {
            return [];
        }
        $filteredPaths = array_filter(array_map(static fn(SplFileInfo $file) => $file->getRealPath(), iterator_to_array($this->sourceFileFilter->filter($this->infectionConfig->getSourceFiles()))));
        return $filteredPaths;
    }
}
