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

namespace Infection\Tests;

use Infection\Container;
use InvalidArgumentException;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
final class ContainerTest extends TestCase
{
    public function test_it_can_be_instantiated_without_any_services(): void
    {
        $container = new Container([]);

        try {
            $container->getFileSystem();

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Unknown service "Symfony\Component\Filesystem\Filesystem"',
                $exception->getMessage()
            );
        }
    }

    public function test_it_can_be_instantiated_with_the_project_services(): void
    {
        $container = SingletonContainer::getContainer();

        $container->getFileSystem();

        $this->addToAssertionCount(1);
    }

    public function test_it_can_build_dynamic_services(): void
    {
        $container = SingletonContainer::getContainer();

        // Sanity check
        try {
            $container->getConfiguration();

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Unknown service "Infection\Configuration\Configuration"',
                $exception->getMessage()
            );
        }

        $newContainer = $container->withDynamicParameters(
            null,
            '',
            false,
            'default',
            false,
            false,
            'dot',
            false,
            '/path/to/coverage',
            '',
            false,
            false,
            .0,
            .0,
            'phpunit',
            '',
            ''
        );

        $newContainer->getSchemaConfiguration();

        $this->addToAssertionCount(1);
    }

    public function test_it_can_build_source_file_data_factory(): void
    {
        $container = SingletonContainer::getContainer();
        $newContainer = $container->withDynamicParameters(
            null,
            '',
            false,
            'default',
            false,
            false,
            'dot',
            false,
            '/path/to/coverage',
            '',
            false,
            false,
            .0,
            .0,
            'phpunit',
            '',
            ''
        );

        $files = $newContainer->getSourceFileDataFactory()->provideFiles();

        $this->assertIsIterable($files);

        $this->expectException(Warning::class);
        $this->expectExceptionMessage('No such file or directory');

        foreach ($files as $file) {
            $this->fail();
        }
    }
}
