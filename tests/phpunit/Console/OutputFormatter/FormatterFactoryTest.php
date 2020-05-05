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

namespace Infection\Tests\Console\OutputFormatter;

use function array_keys;
use function implode;
use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\FormatterFactory;
use Infection\Console\OutputFormatter\FormatterName;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Tests\Fixtures\Console\FakeOutput;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

final class FormatterFactoryTest extends TestCase
{
    /**
     * @dataProvider formatterProvider
     */
    public function test_it_can_create_all_known_factories(
        string $formatterName,
        string $expectedFormatterClassName
    ): void {
        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->method('isDecorated')
            ->willReturn(false)
        ;

        $formatter = (new FormatterFactory($outputMock))->create($formatterName);

        $this->assertInstanceOf($expectedFormatterClassName, $formatter);
    }

    public function test_it_provides_a_friendly_error_message_when_an_unknown_formatter_is_given(): void
    {
        $factory = new FormatterFactory(new FakeOutput());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown formatter "unknown". The known formatters are: "dot", "progress"');

        $factory->create('unknown');
    }

    public static function formatterProvider(): iterable
    {
        $map = [
            FormatterName::DOT => DotFormatter::class,
            FormatterName::PROGRESS => ProgressFormatter::class,
        ];

        Assert::same(
            FormatterName::ALL,
            array_keys($map),
            sprintf(
                'Expected the given map to contain all the known formatters "%s". Got "%s"',
                implode('", "', FormatterName::ALL),
                implode('", "', array_keys($map))
            )
        );

        foreach ($map as $formatterName => $formatterClassName) {
            yield [$formatterName, $formatterClassName];
        }
    }
}
