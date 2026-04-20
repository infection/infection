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

namespace Infection\Tests\Configuration\Entry;

use Infection\Configuration\Entry\Mago;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Mago::class)]
final class MagoTest extends TestCase
{
    #[DataProvider('basePathProvider')]
    public function test_it_can_create_a_new_instance_with_absolute_paths(
        Mago $mago,
        string $basePath,
        Mago $expected,
    ): void {
        $originalMago = clone $mago;

        $actual = $mago->withAbsolutePaths($basePath);

        $this->assertEquals($expected, $actual);
        // Sanity check
        $this->assertEquals($originalMago, $mago);
    }

    public static function basePathProvider(): iterable
    {
        yield 'minimal' => [
            new Mago(null, null),
            '/path/to/project',
            new Mago(
                '/path/to/project',
                null,
            ),
        ];

        yield 'both paths are relative' => [
            new Mago(
                'devTools/mago',
                'devTools/mago/bin/mago',
            ),
            '/path/to/project',
            new Mago(
                '/path/to/project/devTools/mago',
                '/path/to/project/devTools/mago/bin/mago',
            ),
        ];

        yield 'both paths are absolute' => [
            new Mago(
                '/path/to/another-project/devTools/mago',
                '/path/to/another-project/devTools/mago/bin/mago',
            ),
            '/path/to/project',
            new Mago(
                '/path/to/another-project/devTools/mago',
                '/path/to/another-project/devTools/mago/bin/mago',
            ),
        ];
    }
}
