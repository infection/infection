<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Finder;

use Infection\Finder\Exception\FinderException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
class TestFrameworkFinder extends AbstractExecutableFinder
{
    /**
     * @var string
     */
    private $testFrameworkName;

    /**
     * @var string
     */
    private $customPath;

    /**
     * @var string
     */
    private $cachedPath;

    public function __construct(string $testFrameworkName, string $customPath = null)
    {
        $this->testFrameworkName = $testFrameworkName;
        $this->customPath = $customPath;
    }

    public function find(): string
    {
        if ($this->cachedPath === null) {
            if (!$this->shouldUseCustomPath()) {
                $this->addVendorFolderToPath();
            }

            $this->cachedPath = $this->findTestFramework();
        }

        return $this->cachedPath;
    }

    private function shouldUseCustomPath(): bool
    {
        if (null === $this->customPath) {
            return false;
        }

        if (file_exists($this->customPath)) {
            return true;
        }

        throw FinderException::testCustomPathDoesNotExist($this->testFrameworkName, $this->customPath);
    }

    private function addVendorFolderToPath()
    {
        $vendorPath = null;

        try {
            $process = new Process(sprintf('%s %s', $this->findComposer(), 'config bin-dir'));
            $process->mustRun();
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

    private function findTestFramework(): string
    {
        if ($this->shouldUseCustomPath()) {
            return $this->customPath;
        }

        $candidates = [$this->testFrameworkName, $this->testFrameworkName . '.phar'];
        $finder = new ExecutableFinder();

        foreach ($candidates as $name) {
            if ($path = $finder->find($name, null, [getcwd()])) {
                return $path;
            }
        }

        $path = $this->searchNonExecutables($candidates, [getcwd()]);

        if (null !== $path) {
            return $path;
        }

        throw FinderException::testFrameworkNotFound($this->testFrameworkName);
    }
}
