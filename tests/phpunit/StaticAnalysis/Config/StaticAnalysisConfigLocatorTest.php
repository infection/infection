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

namespace Infection\Tests\StaticAnalysis\Config;

use Infection\FileSystem\FileSystem;
use Infection\FileSystem\Locator\FileOrDirectoryNotFound;
use Infection\StaticAnalysis\Config\StaticAnalysisConfigLocator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(StaticAnalysisConfigLocator::class)]
final class StaticAnalysisConfigLocatorTest extends TestCase
{
    private string $baseDir = __DIR__ . '/../../Fixtures/ConfigLocator/';

    public function test_it_looks_up_and_resolves_the_config_file_via_the_injected_file_system(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/custom-dir/phpstan.neon', false],
                ['/custom-dir/phpstan.neon.dist', true],
            ])
        ;
        $fileSystemMock
            ->expects($this->once())
            ->method('realPath')
            ->with('/custom-dir/phpstan.neon.dist')
            ->willReturn('/resolved/phpstan.neon.dist')
        ;

        $locator = new StaticAnalysisConfigLocator('/config-dir', $fileSystemMock);

        $actual = $locator->locate('phpstan', '/custom-dir');

        $this->assertSame('/resolved/phpstan.neon.dist', $actual);
    }

    public function test_it_throws_an_error_if_no_config_file_found(): void
    {
        $dir = $this->baseDir . 'NoFiles/';
        $locator = new StaticAnalysisConfigLocator($dir);

        $this->expectException(FileOrDirectoryNotFound::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not locate the files "phpstan.neon", "phpstan.neon.dist", "phpstan.dist.neon", "phpstan.toml", "phpstan.json", "phpstan.yaml" in "%s"',
                $dir,
            ),
        );

        $locator->locate('phpstan');
    }

    public function test_it_can_find_a_dist_file(): void
    {
        $dir = $this->baseDir . 'DistFile/';
        $locator = new StaticAnalysisConfigLocator($dir);

        $output = $locator->locate('phpstan');

        $this->assertStringEndsWith(
            'tests/phpunit/Fixtures/ConfigLocator/DistFile/phpstan.neon.dist',
            Path::normalize($output),
            'Did not find the correct phpstan.neon.dist file.',
        );
    }

    public function test_it_can_find_an_alt_dist_file(): void
    {
        $dir = $this->baseDir . 'AltDistFile/';
        $locator = new StaticAnalysisConfigLocator($dir);

        $output = $locator->locate('phpstan');

        $this->assertStringEndsWith(
            'tests/phpunit/Fixtures/ConfigLocator/AltDistFile/phpstan.dist.neon',
            Path::normalize($output),
            'Did not find the correct phpstan.dist.neon file.',
        );
    }

    public function test_it_can_find_a_neon_file(): void
    {
        $dir = $this->baseDir . 'NeonFile/';
        $locator = new StaticAnalysisConfigLocator($dir);

        $output = $locator->locate('phpstan');

        $this->assertStringEndsWith(
            'tests/phpunit/Fixtures/ConfigLocator/NeonFile/phpstan.neon',
            Path::normalize($output),
            'Did not find the correct phpstan.neon file.',
        );
    }

    public function test_it_prefers_non_dist_files(): void
    {
        $dir = $this->baseDir . 'BothNeonAndDist/';
        $locator = new StaticAnalysisConfigLocator($dir);

        $output = $locator->locate('phpstan');

        $this->assertStringEndsWith(
            'tests/phpunit/Fixtures/ConfigLocator/BothNeonAndDist/phpstan.neon',
            Path::normalize($output),
            'Did not find the correct phpstan.neon file.',
        );
    }
}
