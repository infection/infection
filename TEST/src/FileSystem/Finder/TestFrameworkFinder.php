<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem\Finder;

use function array_key_exists;
use function dirname;
use function file_exists;
use function getenv;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\Exception\FinderException;
use _HumbugBox9658796bb9f0\Infection\TestFramework\TestFrameworkTypes;
use function ltrim;
use const PATH_SEPARATOR;
use function rtrim;
use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
use function _HumbugBox9658796bb9f0\Safe\getcwd;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function _HumbugBox9658796bb9f0\Safe\putenv;
use function _HumbugBox9658796bb9f0\Safe\realpath;
use function _HumbugBox9658796bb9f0\Safe\substr;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\ExecutableFinder;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Process;
use function trim;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class TestFrameworkFinder
{
    private array $cachedPath = [];
    public function find(string $testFrameworkName, string $customPath = '') : string
    {
        if (!array_key_exists($testFrameworkName, $this->cachedPath)) {
            if (!$this->shouldUseCustomPath($testFrameworkName, $customPath)) {
                $this->addVendorBinToPath();
            }
            $this->cachedPath[$testFrameworkName] = realpath($this->findTestFramework($testFrameworkName, $customPath));
            Assert::string($this->cachedPath[$testFrameworkName]);
            if (substr($this->cachedPath[$testFrameworkName], -4) === '.bat') {
                $this->cachedPath[$testFrameworkName] = $this->findFromBatchFile($this->cachedPath[$testFrameworkName]);
            }
        }
        return $this->cachedPath[$testFrameworkName];
    }
    private function shouldUseCustomPath(string $testFrameworkName, string $customPath) : bool
    {
        if ($customPath === '') {
            return \false;
        }
        if (file_exists($customPath)) {
            return \true;
        }
        throw FinderException::testCustomPathDoesNotExist($testFrameworkName, $customPath);
    }
    private function addVendorBinToPath() : void
    {
        $vendorPath = null;
        try {
            $process = new Process([$this->findComposer(), 'config', 'bin-dir']);
            $process->mustRun();
            $vendorPath = trim($process->getOutput());
        } catch (RuntimeException) {
            $candidate = getcwd() . '/vendor/bin';
            if (file_exists($candidate)) {
                $vendorPath = $candidate;
            }
        }
        if ($vendorPath !== null) {
            $pathName = getenv('PATH') !== \false ? 'PATH' : 'Path';
            putenv($pathName . '=' . $vendorPath . PATH_SEPARATOR . getenv($pathName));
        }
    }
    private function findComposer() : string
    {
        return (new ComposerExecutableFinder())->find();
    }
    private function findTestFramework(string $testFrameworkName, string $customPath) : string
    {
        if ($this->shouldUseCustomPath($testFrameworkName, $customPath)) {
            return $customPath;
        }
        $candidates = [$testFrameworkName . '.bat', $testFrameworkName, $testFrameworkName . '.phar'];
        if ($testFrameworkName === TestFrameworkTypes::PHPUNIT) {
            $candidates[] = 'simple-phpunit.bat';
            $candidates[] = 'simple-phpunit';
            $candidates[] = 'simple-phpunit.phar';
        }
        $finder = new ExecutableFinder();
        $cwd = getcwd();
        $extraDirs = [$cwd, $cwd . '/bin'];
        foreach ($candidates as $name) {
            $path = $finder->find($name, null, $extraDirs);
            if ($path !== null) {
                return $path;
            }
        }
        $nonExecutableFinder = new NonExecutableFinder();
        $path = $nonExecutableFinder->searchNonExecutables($candidates, $extraDirs);
        if ($path !== null) {
            return $path;
        }
        throw FinderException::testFrameworkNotFound($testFrameworkName);
    }
    private function findFromBatchFile(string $path) : string
    {
        if (preg_match('/%~dp0(.+$)/mi', file_get_contents($path), $match) === 1) {
            $target = ltrim(rtrim(trim($match[1]), '" %*'), '\\/');
            $script = realpath(dirname($path) . '/' . $target);
            if (file_exists($script)) {
                $path = $script;
            }
        }
        return $path;
    }
}
