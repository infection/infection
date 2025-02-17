<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
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

namespace Infection\FileSystem\Finder;

use function array_key_exists;
use function dirname;
use function file_exists;
use function getenv;
use Infection\FileSystem\Finder\Exception\FinderException;
use Infection\TestFramework\TestFrameworkTypes;
use function ltrim;
use const PATH_SEPARATOR;
use function rtrim;
use RuntimeException;
use function Safe\file_get_contents;
use function Safe\getcwd;
use function Safe\preg_match;
use function Safe\putenv;
use function Safe\realpath;
use function substr;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
class TestFrameworkFinder
{
    private const BAT_EXTENSION_LENGTH = 4;

    /**
     * @var array<string, string>
     */
    private array $cachedPath = [];

    public function find(string $testFrameworkName, string $customPath = ''): string
    {
        if (!array_key_exists($testFrameworkName, $this->cachedPath)) {
            if (!$this->shouldUseCustomPath($testFrameworkName, $customPath)) {
                $this->addVendorBinToPath();
            }

            $this->cachedPath[$testFrameworkName] = realpath($this->findTestFramework($testFrameworkName, $customPath));

            Assert::string($this->cachedPath[$testFrameworkName]);

            if (substr($this->cachedPath[$testFrameworkName], -self::BAT_EXTENSION_LENGTH) === '.bat') {
                $this->cachedPath[$testFrameworkName] = $this->findFromBatchFile($this->cachedPath[$testFrameworkName]);
            }
        }

        return $this->cachedPath[$testFrameworkName];
    }

    private function shouldUseCustomPath(string $testFrameworkName, string $customPath): bool
    {
        if ($customPath === '') {
            return false;
        }

        if (file_exists($customPath)) {
            return true;
        }

        throw FinderException::testCustomPathDoesNotExist($testFrameworkName, $customPath);
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
        } catch (RuntimeException) {
            $candidate = getcwd() . '/vendor/bin';

            if (file_exists($candidate)) {
                $vendorPath = $candidate;
            }
        }

        if ($vendorPath !== null) {
            $pathName = getenv('PATH') !== false ? 'PATH' : 'Path';
            putenv($pathName . '=' . $vendorPath . PATH_SEPARATOR . getenv($pathName));
        }
    }

    private function findComposer(): string
    {
        return (new ComposerExecutableFinder())->find();
    }

    private function findTestFramework(string $testFrameworkName, string $customPath): string
    {
        if ($this->shouldUseCustomPath($testFrameworkName, $customPath)) {
            return $customPath;
        }

        /*
         * There's a glitch where ExecutableFinder would find a non-executable
         * file on Windows, even if there's a proper executable .bat by its side.
         * Therefore we have to explicitly look for a .bat first.
         */
        $candidates = [
            $testFrameworkName . '.bat',
            $testFrameworkName,
            $testFrameworkName . '.phar',
        ];

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

    private function findFromBatchFile(string $path): string
    {
        /* Check the proxy code (%~dp0 is the script path with a backslash),
         * then trim it and remove any leading directory slash and any trailing
         * components. This will extract the relative path from lines like:
         *
         *   SET BIN_TARGET=%~dp0/../path
         *   php %~dp0/path %*
         */
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
