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

use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Console\OutputFormatter\FormatterFactory;
use Infection\Console\OutputFormatter\FormatterName;
use Infection\Console\OutputFormatter\ProgressFormatter;
use Infection\Framework\Enum\EnumBucket;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(FormatterFactory::class)]
final class FormatterFactoryTest extends TestCase
{
    #[DataProvider('formatterProvider')]
    public function test_it_can_create_all_known_factories(
        FormatterName $formatterName,
        string $expectedFormatterClassName,
    ): void {
        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->method('isDecorated')
            ->willReturn(false)
        ;

        $formatter = (new FormatterFactory($outputMock))->create($formatterName);

        $this->assertInstanceOf($expectedFormatterClassName, $formatter);
    }

    public static function formatterProvider(): iterable
    {
        $bucket = EnumBucket::create(FormatterName::class);

        yield [
            $bucket->take(FormatterName::DOT),
            DotFormatter::class,
        ];

        yield [
            $bucket->take(FormatterName::PROGRESS),
            ProgressFormatter::class,
        ];

        $bucket->assertIsEmpty();
    }
}
