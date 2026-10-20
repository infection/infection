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

namespace Infection\Configuration;

use Infection\Configuration\Entry\Source;
use Infection\FileSystem\Paths;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * Source directories resolved against the Infection configuration directory,
 * with unchanged exclusion rules and the base path used for resolution.
 *
 * @internal
 */
final readonly class ResolvedSource
{
    /**
     * @param list<non-empty-string> $directories Absolute paths resolved against the Infection configuration directory.
     * @param list<non-empty-string> $excludes
     * @param non-empty-string $basePath Absolute path to the Infection configuration directory.
     */
    private function __construct(
        public array $directories,
        public array $excludes,
        public string $basePath,
    ) {
    }

    public static function create(
        Source $source,
        string $basePath,
    ): self {
        Assert::true(
            $basePath !== '' && Path::isAbsolute($basePath),
            'The source base path must be absolute so resolved directories do not depend on the working directory.',
        );

        return new self(
            Paths::makeAbsolute(
                $source->directories,
                $basePath,
            ),
            $source->excludes,
            $basePath,
        );
    }
}
