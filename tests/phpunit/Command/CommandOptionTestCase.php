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

namespace Infection\Tests\Command;

use Exception;
use Infection\Command\Option\CommandOption;
use Infection\Console\IO;
use Infection\Tests\TestingUtility\Console\Command\TestOptionCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;

abstract class CommandOptionTestCase extends TestCase
{
    #[DataProvider('optionProvider')]
    public function test_it_maps_the_option(
        InputInterface $input,
        string|Exception|null $expected,
    ): void {
        $commandOptionClassName = $this->getOptionClassName();
        $io = new IO(
            $input,
            new NullOutput(),
        );

        TestOptionCommand::bind(
            $commandOptionClassName,
            $io,
        );

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $io->getInput()->validate();

        $actual = $commandOptionClassName::get($io);

        if (!($expected instanceof Exception)) {
            $this->assertSame($expected, $actual);
        }
    }

    /**
     * @return iterable<string, array{InputInterface, mixed}>
     */
    abstract public static function optionProvider(): iterable;

    /**
     * @return class-string<CommandOption>
     */
    abstract protected function getOptionClassName(): string;
}
