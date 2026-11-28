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

use Infection\Command\Option\ProgressOption;
use Infection\Tests\Command\CommandOptionTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Input\StringInput;

#[CoversClass(ProgressOption::class)]
final class ProgressOptionTest extends CommandOptionTestCase
{
    public static function optionProvider(): iterable
    {
        yield 'no option leaves the decision to CI detection' => [
            new StringInput(''),
            null,
        ];

        yield 'no progress' => [
            new StringInput('--no-progress'),
            true,
        ];

        yield 'forced progress' => [
            new StringInput('--force-progress'),
            false,
        ];

        yield 'both options' => [
            new StringInput('--no-progress --force-progress'),
            new InvalidArgumentException('Cannot pass both "no-progress" and "force-progress" option: use none or only one of them'),
        ];
    }

    protected function getOptionClassName(): string
    {
        return ProgressOption::class;
    }
}
