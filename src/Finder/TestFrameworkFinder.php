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

    public function __construct(string $testFrameworkName, string $customPath = '')
    {
        $this->testFrameworkName = $testFrameworkName;
        $this->customPath = $customPath;
    }

    public function find(): string
    {
        if (!isset($this->cachedPath)) {
            if (!$this->shouldUseCustomPath()) {
                $this->addVendorFolderToPath();
            }

            $this->cachedPath = (string) realpath($this->findTestFramework());
        }

        return $this->cachedPath;
    }

    private function shouldUseCustomPath(): bool
    {
        if (!$this->customPath) {
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
            $process = new Process([
                $this->findComposer(),
                'config',
                'bin-dir',
            ]);

            $process->mustRun();
            $vendorPath = trim($process->getOutput());
        } catch (\RuntimeException $e) {
            $candidate = getcwd() . '/vendor/bin';
            if (file_exists($candidate)) {
                $vendorPath = $candidate;
            }
        }
        if (null !== $vendorPath) {
            $pathName = getenv('PATH') ? 'PATH' : 'Path';
            putenv($pathName . '=' . $vendorPath . PATH_SEPARATOR . getenv($pathName));
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

        $candidates = [
            $this->testFrameworkName,
            $this->testFrameworkName . '.phar',
        ];

        /*
         * There's a glitch where ExecutableFinder would find a non-executable
         * file on Windows, even if there's a proper executable .bat by its side.
         * Therefore we have to explicitly look for a .bat.
         */
        if ('\\' == \DIRECTORY_SEPARATOR) {
            array_unshift($candidates, $this->testFrameworkName . '.bat');
        } else {
            // yet always looking for .bat for testing with .bat not on Windows
            $candidates[] = $this->testFrameworkName . '.bat';
        }

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
