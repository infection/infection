<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Finder;

use Infection\Finder\Exception\FinderException;
use Infection\TestFramework\TestFrameworkTypes;
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
                $this->addVendorBinToPath();
            }

            $this->cachedPath = (string) realpath($this->findTestFramework());

            if ('.bat' === substr($this->cachedPath, -4)) {
                $this->cachedPath = $this->findFromBatchFile($this->cachedPath);
            }
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

    private function addVendorBinToPath(): void
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

        /*
         * There's a glitch where ExecutableFinder would find a non-executable
         * file on Windows, even if there's a proper executable .bat by its side.
         * Therefore we have to explicitly look for a .bat first.
         */
        if ($this->testFrameworkName === TestFrameworkTypes::PHPUNIT) {
            $candidates[] = 'simple-phpunit.bat';
            $candidates[] = 'simple-phpunit';
            $candidates[] = 'simple-phpunit.phar';
        }

        $candidates = [
            $this->testFrameworkName . '.bat',
            $this->testFrameworkName,
            $this->testFrameworkName . '.phar',
        ];

        $finder = new ExecutableFinder();

        $cwd = getcwd();
        $extraDirs = [$cwd, $cwd . '/bin'];

        foreach ($candidates as $name) {
            if ($path = $finder->find($name, null, $extraDirs)) {
                return $path;
            }
        }

        $path = $this->searchNonExecutables($candidates, $extraDirs);

        if (null !== $path) {
            return $path;
        }

        throw FinderException::testFrameworkNotFound($this->testFrameworkName);
    }

    private function findFromBatchFile(string $path): string
    {
        /* Check the proxy code (%~dp0 is the script path with a backslash),
         * then trim it and remove any leading directory slash and any trailing
         * components. This will extract the relative path from lines like:
         *
         *   SET BIN_TARGET=%~dp0/../path
         *   php %~dp0/path %*
         */
        if (preg_match('/%~dp0(.+$)/mi', (string) file_get_contents($path), $match)) {
            $target = ltrim(rtrim(trim($match[1]), '" %*'), '\\/');
            $script = (string) realpath(\dirname($path) . '/' . $target);

            if (file_exists($script)) {
                $path = $script;
            }
        }

        return $path;
    }
}
