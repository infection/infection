<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Codeception;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function assert;
use function explode;
use function implode;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\MemoryUsageAware;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\StreamWrapper\IncludeInterceptor;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Codeception\Coverage\JUnitTestCaseSorter;
use InvalidArgumentException;
use function is_string;
use const LOCK_EX;
use Phar;
use function preg_match;
use ReflectionClass;
use function _HumbugBox9658796bb9f0\Safe\file_put_contents;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strstr;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
use function trim;
final class CodeceptionAdapter implements MemoryUsageAware, TestFrameworkAdapter
{
    public const COVERAGE_DIR = 'codeception-coverage-xml';
    public const NAME = 'codeception';
    private const DEFAULT_ARGS_AND_OPTIONS = ['--no-colors', '--fail-fast'];
    private string $testFrameworkExecutable;
    private CommandLineBuilder $commandLineBuilder;
    private VersionParser $versionParser;
    private JUnitTestCaseSorter $jUnitTestCaseSorter;
    private Filesystem $filesystem;
    private string $jUnitFilePath;
    private string $tmpDir;
    private string $projectDir;
    private array $originalConfigContentParsed;
    private array $srcDirs;
    private ?string $cachedVersion = null;
    public function __construct(string $testFrameworkExecutable, CommandLineBuilder $commandLineBuilder, VersionParser $versionParser, JUnitTestCaseSorter $jUnitTestCaseSorter, Filesystem $filesystem, string $jUnitFilePath, string $tmpDir, string $projectDir, array $originalConfigContentParsed, array $srcDirs)
    {
        $this->commandLineBuilder = $commandLineBuilder;
        $this->testFrameworkExecutable = $testFrameworkExecutable;
        $this->versionParser = $versionParser;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->tmpDir = $tmpDir;
        $this->jUnitTestCaseSorter = $jUnitTestCaseSorter;
        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
        $this->originalConfigContentParsed = $originalConfigContentParsed;
        $this->srcDirs = $srcDirs;
    }
    public function hasJUnitReport() : bool
    {
        return \true;
    }
    public function testsPass(string $output) : bool
    {
        if (preg_match('/failures!/i', $output) > 0) {
            return \false;
        }
        if (preg_match('/errors!/i', $output) > 0) {
            return \false;
        }
        $isOk = preg_match('/OK\\s\\(/', $output) > 0;
        $isOkWithInfo = preg_match('/OK\\s?,/', $output) > 0;
        $isWarning = preg_match('/warnings!/i', $output) > 0;
        return $isOk || $isOkWithInfo || $isWarning;
    }
    public function getMemoryUsed(string $output) : float
    {
        if (preg_match('/Memory: (\\d+(?:\\.\\d+))\\s*MB/', $output, $match) > 0) {
            return (float) $match[1];
        }
        return -1;
    }
    public function getName() : string
    {
        return self::NAME;
    }
    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage) : array
    {
        $argumentsAndOptions = $this->prepareArgumentsAndOptions($extraOptions);
        return $this->commandLineBuilder->build($this->testFrameworkExecutable, $phpExtraArgs, array_merge($argumentsAndOptions, ['--coverage-phpunit', self::COVERAGE_DIR, '--xml', $this->jUnitFilePath, '-o', "paths: output: {$this->tmpDir}", '-o', sprintf('coverage: enabled: %s', Stringifier::stringifyBoolean(!$skipCoverage)), '-o', sprintf('coverage: include: %s', $this->getCoverageIncludeFiles($skipCoverage)), '-o', 'settings: shuffle: true']));
    }
    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions) : array
    {
        $argumentsAndOptions = $this->prepareArgumentsAndOptions($extraOptions);
        $commandLine = $this->commandLineBuilder->build($this->testFrameworkExecutable, [], $argumentsAndOptions);
        $output = sprintf('%s/%s', $this->tmpDir, $mutationHash);
        $interceptorFilePath = sprintf('%s/interceptor.codeception.%s.php', $this->tmpDir, $mutationHash);
        file_put_contents($interceptorFilePath, $this->createCustomBootstrapWithInterceptor($mutationOriginalFilePath, $mutatedFilePath), LOCK_EX);
        $uniqueTestFilePaths = implode(',', $this->jUnitTestCaseSorter->getUniqueSortedFileNames($coverageTests));
        return array_merge($commandLine, ['--group', 'infection', '--bootstrap', $interceptorFilePath, '-o', "paths: output: {$output}", '-o', 'coverage: enabled: false', '-o', "bootstrap: {$interceptorFilePath}", '-o', "groups: infection: [{$uniqueTestFilePaths}]"]);
    }
    public function getVersion() : string
    {
        if ($this->cachedVersion !== null) {
            return $this->cachedVersion;
        }
        $testFrameworkVersionExecutable = $this->commandLineBuilder->build($this->testFrameworkExecutable, [], ['--version']);
        $process = new Process($testFrameworkVersionExecutable);
        $process->mustRun();
        try {
            $version = $this->versionParser->parse($process->getOutput());
        } catch (InvalidArgumentException $e) {
            $version = 'unknown';
        }
        $this->cachedVersion = $version;
        return $this->cachedVersion;
    }
    public function getInitialTestsFailRecommendations(string $commandLine) : string
    {
        return sprintf('Check the executed command to identify the problem: %s', $commandLine);
    }
    private function getInterceptorFileContent(string $interceptorPath, string $originalFilePath, string $mutatedFilePath) : string
    {
        $infectionPhar = '';
        if (strpos(__FILE__, 'phar:') === 0) {
            $infectionPhar = sprintf('\\Phar::loadPhar("%s", "%s");', str_replace('phar://', '', Phar::running(\true)), 'infection.phar');
        }
        $namespacePrefix = $this->getInterceptorNamespacePrefix();
        return <<<CONTENT
{$infectionPhar}
require_once '{$interceptorPath}';

use {$namespacePrefix}Infection\\StreamWrapper\\IncludeInterceptor;

IncludeInterceptor::intercept('{$originalFilePath}', '{$mutatedFilePath}');
IncludeInterceptor::enable();
CONTENT;
    }
    private function createCustomBootstrapWithInterceptor(string $originalFilePath, string $mutatedFilePath) : string
    {
        $originalBootstrap = $this->getOriginalBootstrapFilePath();
        $bootstrapPlaceholder = $originalBootstrap !== null && strlen($originalBootstrap) > 0 ? "require_once '{$originalBootstrap}';" : '';
        $class = new ReflectionClass(IncludeInterceptor::class);
        $interceptorPath = $class->getFileName();
        $customBootstrap = <<<AUTOLOAD
<?php

%s
%s

AUTOLOAD;
        return sprintf($customBootstrap, $bootstrapPlaceholder, $this->getInterceptorFileContent((string) $interceptorPath, $originalFilePath, $mutatedFilePath));
    }
    private function getOriginalBootstrapFilePath() : ?string
    {
        if (!array_key_exists('bootstrap', $this->originalConfigContentParsed)) {
            return null;
        }
        if ($this->filesystem->isAbsolutePath($this->originalConfigContentParsed['bootstrap'])) {
            return $this->originalConfigContentParsed['bootstrap'];
        }
        return sprintf('%s/%s/%s', $this->projectDir, $this->originalConfigContentParsed['paths']['tests'] ?? 'tests', $this->originalConfigContentParsed['bootstrap']);
    }
    private function getInterceptorNamespacePrefix() : string
    {
        $prefix = strstr(__NAMESPACE__, 'Infection', \true);
        assert(is_string($prefix));
        return $prefix;
    }
    private function prepareArgumentsAndOptions(string $extraOptions) : array
    {
        return array_filter(array_merge(['run'], explode(' ', $extraOptions), self::DEFAULT_ARGS_AND_OPTIONS));
    }
    private function getCoverageIncludeFiles(bool $skipCoverage) : string
    {
        if ($skipCoverage) {
            return Stringifier::stringifyArray([]);
        }
        $coverage = array_merge($this->originalConfigContentParsed['coverage'] ?? [], ['enabled' => \true]);
        $includedFiles = array_key_exists('include', $coverage) ? $coverage['include'] : array_map(static function (string $dir) : string {
            return trim($dir, '/') . '/*.php';
        }, $this->srcDirs);
        return Stringifier::stringifyArray($includedFiles);
    }
}
