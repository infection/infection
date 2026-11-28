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

namespace Infection\TestFramework\PhpUnit\Adapter;

use function implode;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use function Safe\preg_match;
use Webmozart\Assert\Assert;

/**
 * When used as the coverage driver, turbocov only collects the coverage of the
 * paths listed in the `turbocov.collect_paths` setting. It does not guess a
 * default: when the setting is not configured, turbocov refuses to collect any
 * coverage and the PHPUnit run is stopped.
 *
 * The setting is applied when PHP compiles a file, so it cannot be changed with
 * `ini_set()`. It has to be passed to the initial test run process instead.
 *
 * Infection knows which source code it targets and can therefore provide this
 * setting when it is not configured, be it in the PHP configuration or in the
 * initial tests PHP options.
 *
 * @see https://github.com/shipmonk-rnd/turbocov#settings
 *
 * @internal
 * @final
 */
readonly class TurbocovCollectPathsProvider
{
    private const string DEFAULT_COLLECT_PATHS = '';

    // turbocov accepts a list of paths separated by a colon.
    private const string PATH_SEPARATOR = ':';

    /**
     * @param non-empty-array<string> $sourceDirectoryPaths
     * @param string|null $collectPathsIniValue The `turbocov.collect_paths` setting of the Infection process; `null` if turbocov is not loaded in it.
     */
    public function __construct(
        private array $sourceDirectoryPaths,
        private string $initialTestsPhpOptions,
        private ?string $collectPathsIniValue,
    ) {
    }

    /**
     * @param non-empty-array<string> $sourceDirectoryPaths
     */
    public static function create(
        array $sourceDirectoryPaths,
        ?string $initialTestsPhpOptions,
    ): self {
        try {
            $collectPathsIniValue = ini_get('turbocov.collect_paths');
        } catch (InfoException) {
            $collectPathsIniValue = null;
        }

        return new self(
            $sourceDirectoryPaths,
            (string) $initialTestsPhpOptions,
            $collectPathsIniValue,
        );
    }

    public function shouldProvide(): bool
    {
        if ($this->isCollectPathsSetInInitialTestsPhpOptions()) {
            return false;
        }

        if ($this->collectPathsIniValue !== null) {
            return true;
        }

        return $this->isTurbocovLoadedInInitialTestsPhpOptions();
    }

    public function getCollectPaths(): string
    {
        $forwardIniValue = $this->collectPathsIniValue !== null
            && $this->collectPathsIniValue !== self::DEFAULT_COLLECT_PATHS;

        return $forwardIniValue
            ? $this->collectPathsIniValue
            : $this->getCollectPathsFromSourceDirectories();
    }

    private function getCollectPathsFromSourceDirectories(): string
    {
        foreach ($this->sourceDirectoryPaths as $sourceDirectoryPath) {
            Assert::notContains(
                $sourceDirectoryPath,
                self::PATH_SEPARATOR,
                'Cannot configure turbocov coverage: the source directory %s contains %s, which turbocov uses as a path separator. Configure the PHP ini setting `turbocov.collect_paths` explicitly, e.g. with `--initial-tests-php-options="-d turbocov.collect_paths=@paths.txt"`.',
            );
        }

        return implode(self::PATH_SEPARATOR, $this->sourceDirectoryPaths);
    }

    private function isCollectPathsSetInInitialTestsPhpOptions(): bool
    {
        return preg_match('/turbocov\.collect_paths\s*=/', $this->initialTestsPhpOptions) === 1;
    }

    private function isTurbocovLoadedInInitialTestsPhpOptions(): bool
    {
        return preg_match('/extension\s*=\s*\S*turbocov/', $this->initialTestsPhpOptions) === 1;
    }
}
