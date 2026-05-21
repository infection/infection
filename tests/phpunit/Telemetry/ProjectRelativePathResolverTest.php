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

namespace Infection\Tests\Telemetry;

use Infection\Telemetry\ProjectRelativePathResolver;
use Infection\Tests\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectRelativePathResolver::class)]
final class ProjectRelativePathResolverTest extends TestCase
{
    public function test_it_keeps_relative_paths_unchanged(): void
    {
        $resolver = new ProjectRelativePathResolver(
            ConfigurationBuilder::withMinimalTestData()
                ->withProjectDirectory('/path/to/project')
                ->build(),
        );

        $this->assertSame(
            'src/File.php',
            $resolver->resolve('src/File.php'),
        );
    }

    public function test_it_makes_absolute_paths_project_relative(): void
    {
        $resolver = new ProjectRelativePathResolver(
            ConfigurationBuilder::withMinimalTestData()
                ->withProjectDirectory('/path/to/project')
                ->build(),
        );

        $this->assertSame(
            'src/File.php',
            $resolver->resolve('/path/to/project/src/File.php'),
        );
    }

    public function test_it_canonicalizes_absolute_paths_before_resolving_them(): void
    {
        $resolver = new ProjectRelativePathResolver(
            ConfigurationBuilder::withMinimalTestData()
                ->withProjectDirectory('/path/to/project/../project')
                ->build(),
        );

        $this->assertSame(
            'src/File.php',
            $resolver->resolve('/path/to/project/./src/../src/File.php'),
        );
    }

    public function test_it_keeps_paths_outside_the_project_relative_to_the_project_directory(): void
    {
        $resolver = new ProjectRelativePathResolver(
            ConfigurationBuilder::withMinimalTestData()
                ->withProjectDirectory('/path/to/project')
                ->build(),
        );

        $this->assertSame(
            '../vendor/package/File.php',
            $resolver->resolve('/path/to/vendor/package/File.php'),
        );
    }

    public function test_it_caches_resolved_paths(): void
    {
        $resolver = new ProjectRelativePathResolver(
            ConfigurationBuilder::withMinimalTestData()
                ->withProjectDirectory('/path/to/project')
                ->build(),
        );

        $this->assertSame(
            'src/File.php',
            $resolver->resolve('/path/to/project/src/../src/File.php'),
        );
        $this->assertSame(
            'src/File.php',
            $resolver->resolve('/path/to/project/src/../src/File.php'),
        );
    }
}
