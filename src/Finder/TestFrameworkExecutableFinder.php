<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Finder;

use Infection\Finder\Exception\TestFrameworkExecutableFinderNotFound;
use Infection\Process\ExecutableFinder\PhpExecutableFinder;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class TestFrameworkExecutableFinder extends AbstractExecutableFinder
{
    /**
     * @var string
     */
    private $cachedExecutable;

    /**
     * @var string
     */
    private $testFrameworkName;

    /**
     * @var string
     */
    private $customPath;

    /**
     * @var bool
     */
    private $cachedIncludedArgs;

    public function __construct(string $testFrameworkName, string $customPath = null)
    {
        $this->testFrameworkName = $testFrameworkName;
        $this->customPath = $customPath;
    }

    public function find(bool $includeArgs = true): string
    {
        if ($this->cachedExecutable === null || $this->cachedIncludedArgs !== $includeArgs) {
            if (!$this->doesCustomPathExist()) {
                $this->addVendorFolderToPath();
            }

            $this->cachedExecutable = $this->findExecutable($includeArgs);
        }

        $this->cachedIncludedArgs = $includeArgs;

        return $this->cachedExecutable;
    }

    private function addVendorFolderToPath()
    {
        $vendorPath = null;
        try {
            $process = new Process(sprintf('%s %s', $this->findComposer(), 'config bin-dir'));
            $process->run();
            $vendorPath = trim($process->getOutput());
        } catch (\RuntimeException $e) {
            $candidate = getcwd() . '/vendor/bin';
            if (file_exists($candidate)) {
                $vendorPath = $candidate;
            }
        }

        if (null !== $vendorPath) {
            putenv('PATH=' . $vendorPath . PATH_SEPARATOR . getenv('PATH'));
        }
    }

    private function findComposer(): string
    {
        return (new ComposerExecutableFinder())->find();
    }

    private function findExecutable(bool $includeArgs = true): string
    {
        if ($this->doesCustomPathExist()) {
            return $this->makeExecutable($this->customPath, $includeArgs);
        }

        $candidates = [$this->testFrameworkName, $this->testFrameworkName . '.phar'];
        $finder = new ExecutableFinder();

        foreach ($candidates as $name) {
            if ($path = $finder->find($name, null, [getcwd()])) {
                return $this->makeExecutable($path, $includeArgs);
            }
        }

        $result = $this->searchNonExecutables($candidates, [getcwd()], $includeArgs);

        if (null !== $result) {
            return $result;
        }

        throw new TestFrameworkExecutableFinderNotFound(
            sprintf(
                'Unable to locate a %s executable on local system. Ensure that %s is installed and available.',
                $this->testFrameworkName,
                $this->testFrameworkName
            )
        );
    }

    /**
     * Prefix commands with exec outside Windows to ensure process timeouts
     * are enforced and end PHP processes properly
     *
     * @param string $path
     * @param bool $includeArgs
     *
     * @return string
     */
    protected function makeExecutable(string $path, bool $includeArgs = true): string
    {
        $path = realpath($path);
        $phpFinder = new PhpExecutableFinder();

        if (\defined('PHP_WINDOWS_VERSION_BUILD')) {
            if (false !== strpos($path, '.bat')) {
                return $path;
            }

            return sprintf('%s %s', $phpFinder->find($includeArgs), $path);
        }

        return sprintf('%s %s %s', 'exec', $phpFinder->find($includeArgs), $path);
    }

    private function doesCustomPathExist(): bool
    {
        return $this->customPath && file_exists($this->customPath);
    }
}
