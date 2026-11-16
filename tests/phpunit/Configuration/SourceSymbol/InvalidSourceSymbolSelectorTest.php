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

namespace Infection\Tests\Configuration\SourceSymbol;

use Infection\Configuration\SourceSymbol\InvalidSourceSymbolSelector;
use const PHP_INT_MAX;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidSourceSymbolSelector::class)]
final class InvalidSourceSymbolSelectorTest extends TestCase
{
    public function test_it_creates_an_exception_for_the_value(): void
    {
        $exception = InvalidSourceSymbolSelector::create('App\Mailer::send:32');

        $this->assertSame(
            <<<'MESSAGE'
                Invalid source selector "App\Mailer::send:32". Expected one of:
                - Class
                - Class::method
                - Class::line
                - Class::method::line
                MESSAGE,
            $exception->getMessage(),
            'The selector error must explain the invalid value and the accepted coordinates.',
        );
    }

    public function test_it_explains_the_line_number_limit(): void
    {
        $exception = InvalidSourceSymbolSelector::lineOutOfRange('App\Mailer::send::99999999999999999999');

        $this->assertSame(
            'Invalid source selector "App\Mailer::send::99999999999999999999": the line number must not exceed ' . PHP_INT_MAX . '.',
            $exception->getMessage(),
            'An overflowing line must report the maximum supported integer.',
        );
    }
}
