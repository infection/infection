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

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\TimeoutProvider;
use Infection\Console\IO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function Safe\rewind;
use function Safe\stream_get_contents;

#[Group('integration')]
#[CoversClass(TimeoutProvider::class)]
final class TimeoutProviderTest extends BaseProviderTestCase
{
    private TimeoutProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new TimeoutProvider(
            $this->createStub(ConsoleHelper::class),
            $this->getQuestionHelper(),
        );
    }

    public function test_it_uses_default_value(): void
    {
        $output = $this->createStreamOutput();

        $timeout = $this->provider->get(
            new IO(
                $this->createStreamableInput($this->getInputStream("\n")),
                $output,
            ),
        );

        $this->assertSame(TimeoutProvider::DEFAULT_TIMEOUT, $timeout);

        $stream = $output->getStream();
        rewind($stream);
        $display = stream_get_contents($stream);

        $this->assertStringContainsString(
            'Infection limits how long each mutant test process is allowed to run.',
            $display,
        );
    }

    public function test_it_uses_default_value_when_whitespace_is_provided(): void
    {
        $timeout = $this->provider->get(
            new IO(
                $this->createStreamableInput($this->getInputStream("   \n")),
                $this->createStreamOutput(),
            ),
        );

        $this->assertSame(TimeoutProvider::DEFAULT_TIMEOUT, $timeout);
    }

    public function test_it_uses_typed_integer(): void
    {
        $timeout = $this->provider->get(
            new IO(
                $this->createStreamableInput($this->getInputStream("15\n")),
                $this->createStreamOutput(),
            ),
        );

        $this->assertSame(15, $timeout);
    }

    public function test_it_uses_typed_float(): void
    {
        $timeout = $this->provider->get(
            new IO(
                $this->createStreamableInput($this->getInputStream("2.5\n")),
                $this->createStreamOutput(),
            ),
        );

        $this->assertSame(2.5, $timeout);
    }

    #[DataProvider('invalidTimeoutProvider')]
    public function test_validates_incorrect_value(string $invalidInput): void
    {
        $output = $this->createStreamOutput();

        $timeout = $this->provider->get(
            new IO(
                $this->createStreamableInput($this->getInputStream("{$invalidInput}\n15\n")),
                $output,
            ),
        );

        $this->assertSame(15, $timeout);

        $stream = $output->getStream();
        rewind($stream);
        $display = stream_get_contents($stream);

        $this->assertStringContainsString('The timeout must be a positive number.', $display);
    }

    public function test_validates_incorrect_value_then_accepts_default(): void
    {
        $output = $this->createStreamOutput();

        $timeout = $this->provider->get(
            new IO(
                $this->createStreamableInput($this->getInputStream("invalid\n\n")),
                $output,
            ),
        );

        $this->assertSame(TimeoutProvider::DEFAULT_TIMEOUT, $timeout);

        $stream = $output->getStream();
        rewind($stream);
        $display = stream_get_contents($stream);

        $this->assertStringContainsString('The timeout must be a positive number.', $display);
    }

    public static function invalidTimeoutProvider(): iterable
    {
        yield 'non-numeric' => ['invalid'];

        yield 'zero' => ['0'];

        yield 'negative' => ['-5'];
    }
}
