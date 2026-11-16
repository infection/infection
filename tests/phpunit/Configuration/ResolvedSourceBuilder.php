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

namespace Infection\Tests\Configuration;

use function array_values;
use Infection\Configuration\ResolvedSource;

final class ResolvedSourceBuilder
{
    /**
     * @param list<non-empty-string> $directories Absolute source directory paths.
     * @param list<non-empty-string> $excludes
     * @param non-empty-string $basePath
     */
    private function __construct(
        private array $directories,
        private array $excludes,
        private string $basePath,
    ) {
    }

    public static function from(ResolvedSource $source): self
    {
        return new self(
            $source->directories,
            $source->excludes,
            $source->basePath,
        );
    }

    public static function withMinimalTestData(): self
    {
        return new self(
            directories: [],
            excludes: [],
            basePath: '/path/to/project',
        );
    }

    public static function withCompleteTestData(): self
    {
        return new self(
            directories: ['/path/to/project/src', '/path/to/project/lib'],
            excludes: ['vendor', 'tests'],
            basePath: '/path/to/project',
        );
    }

    /**
     * @param non-empty-string ...$directories Absolute source directory paths.
     */
    public function withDirectories(string ...$directories): self
    {
        $clone = clone $this;
        $clone->directories = array_values($directories);

        return $clone;
    }

    /**
     * @param non-empty-string ...$excludes
     */
    public function withExcludes(string ...$excludes): self
    {
        $clone = clone $this;
        $clone->excludes = array_values($excludes);

        return $clone;
    }

    /**
     * @param non-empty-string $basePath
     */
    public function withBasePath(string $basePath): self
    {
        $clone = clone $this;
        $clone->basePath = $basePath;

        return $clone;
    }

    public function build(): ResolvedSource
    {
        return new ResolvedSource(
            $this->directories,
            $this->excludes,
            $this->basePath,
        );
    }
}
