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

namespace Infection\Tests\AutoReview;

use function array_map;
use function in_array;
use Infection\Tests\SingletonContainer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function Safe\file_get_contents;
use function Safe\sprintf;
use function strpos;
use Webmozart\PathUtil\Path;

final class ContainerTest extends TestCase
{
    /**
     * @var string[]|null
     */
    private static $containerFiles;

    /**
     * @dataProvider \Infection\Tests\AutoReview\ProjectCode\ProjectCodeProvider::classesTestProvider
     *
     * @param class-string $className
     */
    public function test_source_class_provider_is_valid(string $className): void
    {
        $classFile = (new ReflectionClass($className))->getFileName();

        $this->assertNotFalse(
            $classFile,
            sprintf('Expected the class "%s" to have a file', $className)
        );

        if (in_array($classFile, $this->getContainerFiles(), true)) {
            return;
        }

        $this->assertFalse(
            strpos(file_get_contents($classFile), 'use Infection\Container;'),
            sprintf(
                'Did not expect to find a usage of the Infection container in "%s". Please use'
                . ' "%s::getContainer() instead',
                $classFile,
                SingletonContainer::class
            )
        );
    }

    /**
     * @return string[]
     */
    public function getContainerFiles(): array
    {
        if (self::$containerFiles !== null) {
            return self::$containerFiles;
        }

        self::$containerFiles = array_map(
            static function (string $path): string {
                return Path::canonicalize($path);
            },
            [
                __DIR__ . '/ContainerTest.php',
                __DIR__ . '/../ContainerTest.php',
                __DIR__ . '/../SingletonContainer.php',
            ]
        );

        return self::$containerFiles;
    }
}
