<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Finder;

use Infection\Finder\Exception\TestFrameworkExecutableFinderNotFound;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class TestFrameworkExecutableFinder extends AbstractExecutableFinder
{
    /**
     * @var string
     */
    private $cachedExecutable;

    /**
     * @var
     */
    private $testFrameworkName;

    /**
     * @var string
     */
    private $customPath;

    public function __construct(string $testFrameworkName, string $customPath = null)
    {
        $this->testFrameworkName = $testFrameworkName;
        $this->customPath = $customPath;
    }

    /**
     * @return string
     */
    public function find()
    {
        if ($this->cachedExecutable === null) {
            $this->addVendorFolderToPath();
            $this->cachedExecutable = $this->findExecutable();
        }

        return $this->cachedExecutable;
    }

    /**
     * @return void
     */
    private function addVendorFolderToPath()
    {
        $vendorPath = null;
        try {
            $composer = $this->findComposer();
            $process = new Process(sprintf('%s %s', $composer, 'config bin-dir'));
            $process->run();
            $vendorPath = trim($process->getOutput());
        } catch (\RuntimeException $e) {
            $candidate = getcwd() . '/vendor/bin';
            if (file_exists($candidate)) {
                $vendorPath = $candidate;
            }
        }

        if (!is_null($vendorPath)) {
            putenv('PATH=' . $vendorPath . PATH_SEPARATOR . getenv('PATH'));
        }
    }

    /**
     * @return string
     */
    private function findComposer()
    {
        $finder = new ComposerExecutableFinder();

        return $finder->find();
    }

    /**
     * @return string
     * @throws TestFrameworkExecutableFinderNotFound
     */
    private function findExecutable()
    {
        if ($this->customPath && file_exists($this->customPath)) {
            return $this->makeExecutable($this->customPath);
        }

        $candidates = [$this->testFrameworkName, $this->testFrameworkName . '.phar'];
        $finder = new ExecutableFinder();

        foreach ($candidates as $name) {
            if ($path = $finder->find($name, null, [getcwd()])) {
                return $this->makeExecutable($path);
            }
        }

        $result = $this->searchNonExecutables($candidates, [getcwd()]);

        if (!is_null($result)) {
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
     * @return string
     */
    protected function makeExecutable($path)
    {
        $path = realpath($path);
        $phpFinder = new PhpExecutableFinder();

        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            if (false !== strpos($path, '.bat')) {
                return $path;
            }
            return sprintf('%s %s', $phpFinder->find(), $path);
        }

        return sprintf('%s %s %s', 'exec', $phpFinder->find(), $path);
    }
}