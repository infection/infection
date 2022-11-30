<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration;

use function array_fill_keys;
use function array_key_exists;
use function array_unique;
use function array_values;
use function dirname;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\Logs;
use _HumbugBox9658796bb9f0\Infection\Configuration\Entry\PhpUnit;
use _HumbugBox9658796bb9f0\Infection\Configuration\Schema\SchemaConfiguration;
use _HumbugBox9658796bb9f0\Infection\FileSystem\SourceFileCollector;
use _HumbugBox9658796bb9f0\Infection\FileSystem\TmpDirProvider;
use _HumbugBox9658796bb9f0\Infection\Logger\GitHub\GitDiffFileProvider;
use _HumbugBox9658796bb9f0\Infection\Mutator\ConfigurableMutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorFactory;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorParser;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorResolver;
use _HumbugBox9658796bb9f0\Infection\TestFramework\TestFrameworkTypes;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetectorInterface;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Exception\CiNotDetectedException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Path;
use function sys_get_temp_dir;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class ConfigurationFactory
{
    private const DEFAULT_TIMEOUT = 10;
    private TmpDirProvider $tmpDirProvider;
    private MutatorResolver $mutatorResolver;
    private MutatorFactory $mutatorFactory;
    private MutatorParser $mutatorParser;
    private SourceFileCollector $sourceFileCollector;
    private CiDetectorInterface $ciDetector;
    private GitDiffFileProvider $gitDiffFileProvider;
    public function __construct(TmpDirProvider $tmpDirProvider, MutatorResolver $mutatorResolver, MutatorFactory $mutatorFactory, MutatorParser $mutatorParser, SourceFileCollector $sourceFileCollector, CiDetectorInterface $ciDetector, GitDiffFileProvider $gitDiffFileProvider)
    {
        $this->tmpDirProvider = $tmpDirProvider;
        $this->mutatorResolver = $mutatorResolver;
        $this->mutatorFactory = $mutatorFactory;
        $this->mutatorParser = $mutatorParser;
        $this->sourceFileCollector = $sourceFileCollector;
        $this->ciDetector = $ciDetector;
        $this->gitDiffFileProvider = $gitDiffFileProvider;
    }
    public function create(SchemaConfiguration $schema, ?string $existingCoveragePath, ?string $initialTestsPhpOptions, bool $skipInitialTests, string $logVerbosity, bool $debug, bool $onlyCovered, bool $noProgress, ?bool $ignoreMsiWithNoMutations, ?float $minMsi, bool $showMutations, ?float $minCoveredMsi, int $msiPrecision, string $mutatorsInput, ?string $testFramework, ?string $testFrameworkExtraOptions, string $filter, int $threadCount, bool $dryRun, ?string $gitDiffFilter, bool $isForGitDiffLines, ?string $gitDiffBase, ?bool $useGitHubLogger, ?string $htmlLogFilePath, bool $useNoopMutators, bool $executeOnlyCoveringTestCases) : Configuration
    {
        $configDir = dirname($schema->getFile());
        $namespacedTmpDir = $this->retrieveTmpDir($schema, $configDir);
        $testFramework = $testFramework ?? $schema->getTestFramework() ?? TestFrameworkTypes::PHPUNIT;
        $skipCoverage = $existingCoveragePath !== null;
        $coverageBasePath = self::retrieveCoverageBasePath($existingCoveragePath, $configDir, $namespacedTmpDir);
        $resolvedMutatorsArray = $this->resolveMutators($schema->getMutators(), $mutatorsInput);
        $mutators = $this->mutatorFactory->create($resolvedMutatorsArray, $useNoopMutators);
        $ignoreSourceCodeMutatorsMap = $this->retrieveIgnoreSourceCodeMutatorsMap($resolvedMutatorsArray);
        return new Configuration($schema->getTimeout() ?? self::DEFAULT_TIMEOUT, $schema->getSource()->getDirectories(), $this->sourceFileCollector->collectFiles($schema->getSource()->getDirectories(), $schema->getSource()->getExcludes()), $this->retrieveFilter($filter, $gitDiffFilter, $isForGitDiffLines, $gitDiffBase, $schema->getSource()->getDirectories()), $schema->getSource()->getExcludes(), $this->retrieveLogs($schema->getLogs(), $useGitHubLogger, $htmlLogFilePath), $logVerbosity, $namespacedTmpDir, $this->retrievePhpUnit($schema, $configDir), $mutators, $testFramework, $schema->getBootstrap(), $initialTestsPhpOptions ?? $schema->getInitialTestsPhpOptions(), self::retrieveTestFrameworkExtraOptions($testFrameworkExtraOptions, $schema), $coverageBasePath, $skipCoverage, $skipInitialTests, $debug, $onlyCovered, $this->retrieveNoProgress($noProgress), self::retrieveIgnoreMsiWithNoMutations($ignoreMsiWithNoMutations, $schema), self::retrieveMinMsi($minMsi, $schema), $showMutations, self::retrieveMinCoveredMsi($minCoveredMsi, $schema), $msiPrecision, $threadCount, $dryRun, $ignoreSourceCodeMutatorsMap, $executeOnlyCoveringTestCases, $isForGitDiffLines, $gitDiffBase);
    }
    /**
     * @param array<string, mixed> $schemaMutators
     *
     * @return array<class-string<Mutator<\PhpParser\Node>&ConfigurableMutator<\PhpParser\Node>>, mixed[]>
     */
    private function resolveMutators(array $schemaMutators, string $mutatorsInput) : array
    {
        if ($schemaMutators === []) {
            $schemaMutators = ['@default' => \true];
        }
        $parsedMutatorsInput = $this->mutatorParser->parse($mutatorsInput);
        if ($parsedMutatorsInput === []) {
            $mutatorsList = $schemaMutators;
        } else {
            $mutatorsList = array_fill_keys($parsedMutatorsInput, \true);
        }
        return $this->mutatorResolver->resolve($mutatorsList);
    }
    private function retrieveTmpDir(SchemaConfiguration $schema, string $configDir) : string
    {
        $tmpDir = (string) $schema->getTmpDir();
        if ($tmpDir === '') {
            $tmpDir = sys_get_temp_dir();
        } elseif (!Path::isAbsolute($tmpDir)) {
            $tmpDir = sprintf('%s/%s', $configDir, $tmpDir);
        }
        return $this->tmpDirProvider->providePath($tmpDir);
    }
    private function retrievePhpUnit(SchemaConfiguration $schema, string $configDir) : PhpUnit
    {
        $phpUnit = clone $schema->getPhpUnit();
        $phpUnitConfigDir = $phpUnit->getConfigDir();
        if ($phpUnitConfigDir === null) {
            $phpUnit->setConfigDir($configDir);
        } elseif (!Path::isAbsolute($phpUnitConfigDir)) {
            $phpUnit->setConfigDir(sprintf('%s/%s', $configDir, $phpUnitConfigDir));
        }
        return $phpUnit;
    }
    private static function retrieveCoverageBasePath(?string $existingCoveragePath, string $configDir, string $tmpDir) : string
    {
        if ($existingCoveragePath === null) {
            return $tmpDir;
        }
        if (Path::isAbsolute($existingCoveragePath)) {
            return $existingCoveragePath;
        }
        return sprintf('%s/%s', $configDir, $existingCoveragePath);
    }
    private static function retrieveTestFrameworkExtraOptions(?string $testFrameworkExtraOptions, SchemaConfiguration $schema) : string
    {
        return $testFrameworkExtraOptions ?? $schema->getTestFrameworkExtraOptions() ?? '';
    }
    private function retrieveNoProgress(bool $noProgress) : bool
    {
        return $noProgress || $this->ciDetector->isCiDetected();
    }
    private static function retrieveIgnoreMsiWithNoMutations(?bool $ignoreMsiWithNoMutations, SchemaConfiguration $schema) : bool
    {
        return $ignoreMsiWithNoMutations ?? $schema->getIgnoreMsiWithNoMutations() ?? \false;
    }
    private static function retrieveMinMsi(?float $minMsi, SchemaConfiguration $schema) : ?float
    {
        return $minMsi ?? $schema->getMinMsi();
    }
    private static function retrieveMinCoveredMsi(?float $minCoveredMsi, SchemaConfiguration $schema) : ?float
    {
        return $minCoveredMsi ?? $schema->getMinCoveredMsi();
    }
    private function retrieveIgnoreSourceCodeMutatorsMap(array $resolvedMutatorsMap) : array
    {
        $map = [];
        foreach ($resolvedMutatorsMap as $mutatorClassName => $config) {
            if (array_key_exists('ignoreSourceCodeByRegex', $config)) {
                $mutatorName = MutatorFactory::getMutatorNameForClassName($mutatorClassName);
                Assert::isArray($config['ignoreSourceCodeByRegex']);
                $map[$mutatorName] = array_values(array_unique($config['ignoreSourceCodeByRegex']));
            }
        }
        return $map;
    }
    private function retrieveFilter(string $filter, ?string $gitDiffFilter, bool $isForGitDiffLines, ?string $gitDiffBase, array $sourceDirectories) : string
    {
        if ($gitDiffFilter === null && !$isForGitDiffLines) {
            return $filter;
        }
        $baseBranch = $gitDiffBase ?? GitDiffFileProvider::DEFAULT_BASE;
        if ($isForGitDiffLines) {
            return $this->gitDiffFileProvider->provide('AM', $baseBranch, $sourceDirectories);
        }
        return $this->gitDiffFileProvider->provide($gitDiffFilter, $baseBranch, $sourceDirectories);
    }
    private function retrieveLogs(Logs $logs, ?bool $useGitHubLogger, ?string $htmlLogFilePath) : Logs
    {
        if ($useGitHubLogger === null) {
            $useGitHubLogger = $this->detectCiGithubActions();
        }
        if ($useGitHubLogger) {
            $logs->setUseGitHubAnnotationsLogger($useGitHubLogger);
        }
        if ($htmlLogFilePath !== null) {
            $logs->setHtmlLogFilePath($htmlLogFilePath);
        }
        return $logs;
    }
    private function detectCiGithubActions() : bool
    {
        try {
            $ci = $this->ciDetector->detect();
        } catch (CiNotDetectedException $e) {
            return \false;
        }
        return $ci->getCiName() === CiDetector::CI_GITHUB_ACTIONS;
    }
}
