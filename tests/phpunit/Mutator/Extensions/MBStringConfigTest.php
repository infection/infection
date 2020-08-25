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

namespace Infection\Tests\Mutator\Extensions;

use Infection\Mutator\Extensions\MBStringConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MBStringConfigTest extends TestCase
{
    /**
     * @dataProvider settingsProvider
     */
    public function test_it_can_create_a_config(array $settings, array $expected): void
    {
        $config = new MBStringConfig($settings);

        $this->assertSame($expected, $config->getAllowedFunctions());
    }

    public function test_its_settings_must_be_boolean_values(): void
    {
        try {
            new MBStringConfig(['foo' => 'bar']);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected the value for "foo" to be a boolean. Got "string" instead',
                $exception->getMessage()
            );
        }
    }

    public function test_it_must_be_a_known_function(): void
    {
        try {
            new MBStringConfig(['foo' => true]);

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Expected one of: "mb_chr", "mb_ord", "mb_parse_str", "mb_send_mail", "mb_strcut", "mb_stripos", "mb_stristr", "mb_strlen", "mb_strpos", "mb_strrchr", "mb_strripos", "mb_strrpos", "mb_strstr", "mb_strtolower", "mb_strtoupper", "mb_str_split", "mb_substr_count", "mb_substr", "mb_convert_case". Got: "foo"',
                $exception->getMessage()
            );
        }
    }

    public function settingsProvider(): iterable
    {
        yield 'default' => [
            [],
            [
                'mb_chr',
                'mb_ord',
                'mb_parse_str',
                'mb_send_mail',
                'mb_strcut',
                'mb_stripos',
                'mb_stristr',
                'mb_strlen',
                'mb_strpos',
                'mb_strrchr',
                'mb_strripos',
                'mb_strrpos',
                'mb_strstr',
                'mb_strtolower',
                'mb_strtoupper',
                'mb_str_split',
                'mb_substr_count',
                'mb_substr',
                'mb_convert_case',
            ],
        ];

        yield 'one function enabled' => [
            ['mb_chr' => true],
            [
                'mb_chr',
                'mb_ord',
                'mb_parse_str',
                'mb_send_mail',
                'mb_strcut',
                'mb_stripos',
                'mb_stristr',
                'mb_strlen',
                'mb_strpos',
                'mb_strrchr',
                'mb_strripos',
                'mb_strrpos',
                'mb_strstr',
                'mb_strtolower',
                'mb_strtoupper',
                'mb_str_split',
                'mb_substr_count',
                'mb_substr',
                'mb_convert_case',
            ],
        ];

        yield 'one function disabled' => [
            ['mb_chr' => false],
            [
                'mb_ord',
                'mb_parse_str',
                'mb_send_mail',
                'mb_strcut',
                'mb_stripos',
                'mb_stristr',
                'mb_strlen',
                'mb_strpos',
                'mb_strrchr',
                'mb_strripos',
                'mb_strrpos',
                'mb_strstr',
                'mb_strtolower',
                'mb_strtoupper',
                'mb_str_split',
                'mb_substr_count',
                'mb_substr',
                'mb_convert_case',
            ],
        ];
    }
}
