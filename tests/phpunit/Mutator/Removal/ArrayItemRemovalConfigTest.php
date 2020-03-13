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

namespace Infection\Tests\Mutator\Removal;

use Infection\Mutator\Removal\ArrayItemRemovalConfig;
use InvalidArgumentException;
use const PHP_INT_MAX;
use PHPUnit\Framework\TestCase;

final class ArrayItemRemovalConfigTest extends TestCase
{
    /**
     * @dataProvider settingsProvider
     */
    public function test_it_can_create_a_config(
        array $settings,
        string $expectedRemove,
        int $expectedLimit
    ): void {
        $config = new ArrayItemRemovalConfig($settings);

        $this->assertSame($expectedRemove, $config->getRemove());
        $this->assertSame($expectedLimit, $config->getLimit());
    }

    public function test_the_remove_value_must_be_a_known_value(): void
    {
        try {
            new ArrayItemRemovalConfig(['remove' => 'unknown']);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected one of: "first", "last", "all". Got: "unknown"',
                $exception->getMessage()
            );
        }
    }

    public function test_the_limit_must_be_an_integer(): void
    {
        try {
            new ArrayItemRemovalConfig(['limit' => 'foo']);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected the limit to be an integer. Got "string" instead',
                $exception->getMessage()
            );
        }
    }

    public function test_the_limit_must_be_equal_or_greater_than_1(): void
    {
        try {
            new ArrayItemRemovalConfig(['limit' => 0]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected the limit to be greater or equal than 1. Got "0" instead',
                $exception->getMessage()
            );
        }
    }

    public function settingsProvider(): iterable
    {
        yield 'default' => [
            [],
            'first',
            PHP_INT_MAX,
        ];

        yield 'setting the remove at the default value' => [
            ['remove' => 'first'],
            'first',
            PHP_INT_MAX,
        ];

        yield 'setting the remove at a different value' => [
            ['remove' => 'last'],
            'last',
            PHP_INT_MAX,
        ];

        yield 'setting the limit at the default value' => [
            ['limit' => PHP_INT_MAX],
            'first',
            PHP_INT_MAX,
        ];

        yield 'setting the limit at a different value' => [
            ['limit' => 1],
            'first',
            1,
        ];

        yield 'setting both values' => [
            [
                'remove' => 'last',
                'limit' => 1,
            ],
            'last',
            1,
        ];
    }
}
