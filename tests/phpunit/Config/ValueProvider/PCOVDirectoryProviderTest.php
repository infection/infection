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

namespace Infection\Tests\Config\ValueProvider;

use Infection\Config\ValueProvider\PCOVDirectoryProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use function Safe\ini_get;

#[CoversClass(PCOVDirectoryProvider::class)]
final class PCOVDirectoryProviderTest extends TestCase
{
    public function test_it_provides_a_directory_when_pcov_directory_is_unset(): void
    {
        $provider = new PCOVDirectoryProvider('');

        $this->assertTrue($provider->shouldProvide());
        $this->assertSame('.', $provider->getDirectory());
    }

    public function test_it_does_not_provide_a_directory_when_pcov_directory_is_configured(): void
    {
        $provider = new PCOVDirectoryProvider('example');

        $this->assertFalse($provider->shouldProvide());
    }

    #[RequiresPhpExtension('pcov')]
    public function test_it_reads_pcov_directory_from_the_ini_configuration(): void
    {
        $provider = new PCOVDirectoryProvider();

        // Note that `pcov.directory` is a `PHP_INI_SYSTEM | PHP_INI_PERDIR` so
        // it cannot be set at runtime.
        // As a result, this test is a bit light, but being more thorough would
        // be too expensive infrastructure-wise.
        // See https://github.com/krakjoe/pcov/blob/57e143363aa6ba3c4d1e1b0a2e68556e28f38950/pcov.c#L80-L83
        $expected = ini_get('pcov.directory') === '';

        $this->assertSame($expected, $provider->shouldProvide());
    }
}
