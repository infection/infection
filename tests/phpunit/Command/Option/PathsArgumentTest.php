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

namespace Infection\Tests\Command\Option;

use Infection\Command\Option\PathsArgument;
use Infection\Console\IO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(PathsArgument::class)]
final class PathsArgumentTest extends TestCase
{
    public function test_it_adds_two_optional_arguments(): void
    {
        $command = new Command('test');

        $returnedCommand = PathsArgument::addArgument($command);

        $this->assertSame($command, $returnedCommand);
        $this->assertTrue($command->getDefinition()->hasArgument(PathsArgument::SLOT_1_NAME));
        $this->assertTrue($command->getDefinition()->hasArgument(PathsArgument::SLOT_2_NAME));
        $this->assertFalse($command->getDefinition()->getArgument(PathsArgument::SLOT_1_NAME)->isRequired());
        $this->assertFalse($command->getDefinition()->getArgument(PathsArgument::SLOT_2_NAME)->isRequired());
    }

    #[DataProvider('slot1Provider')]
    public function test_it_reads_slot1(array $arguments, array $expected): void
    {
        $io = $this->createIo(new ArrayInput($arguments));

        $this->assertSame($expected, PathsArgument::getSlot1($io));
    }

    public static function slot1Provider(): iterable
    {
        yield 'not provided' => [
            [],
            [],
        ];

        yield 'blank value' => [
            [
                PathsArgument::SLOT_1_NAME => '   ',
            ],
            [],
        ];

        yield 'single value' => [
            [
                PathsArgument::SLOT_1_NAME => 'src/Foo.php',
            ],
            ['src/Foo.php'],
        ];

        yield 'comma separated values are trimmed and empty items removed' => [
            [
                PathsArgument::SLOT_1_NAME => ' src/Foo.php, ,src/Bar.php ,',
            ],
            ['src/Foo.php', 'src/Bar.php'],
        ];
    }

    #[DataProvider('slot2Provider')]
    public function test_it_reads_slot2(array $arguments, array $expected): void
    {
        $io = $this->createIo(new ArrayInput($arguments));

        $this->assertSame($expected, PathsArgument::getSlot2($io));
    }

    public static function slot2Provider(): iterable
    {
        yield 'not provided' => [
            [],
            [],
        ];

        yield 'blank value' => [
            [
                PathsArgument::SLOT_2_NAME => '   ',
            ],
            [],
        ];

        yield 'single value' => [
            [
                PathsArgument::SLOT_2_NAME => 'tests/FooTest.php',
            ],
            ['tests/FooTest.php'],
        ];

        yield 'comma separated values are trimmed and empty items removed' => [
            [
                PathsArgument::SLOT_2_NAME => ' tests/FooTest.php, ,tests/BarTest.php ,',
            ],
            ['tests/FooTest.php', 'tests/BarTest.php'],
        ];
    }

    private function createIo(InputInterface $input): IO
    {
        $io = new IO($input, new NullOutput());
        $command = new class extends Command {
            protected function configure(): void
            {
                PathsArgument::addArgument($this);
            }
        };

        $input->bind($command->getDefinition());
        $input->validate();

        return $io;
    }
}
