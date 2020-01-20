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

use Generator;
use Infection\Config\Exception\InvalidConfigException;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

final class ArrayItemRemovalTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider mutationsProvider
     *
     * @param string|string[] $expected
     */
    public function test_it_can_mutate(string $input, $expected = [], array $settings = []): void
    {
        $this->doTest($input, $expected, $settings);
    }

    public function mutationsProvider(): Generator
    {
        yield 'It does not mutate empty arrays' => [
            '<?php $a = [];',
        ];

        yield 'It removes only first item by default' => [
            '<?php $a = [1, 2, 3];',
            "<?php\n\n\$a = [2, 3];",
        ];

        yield 'It removes only last item when set to do so' => [
            '<?php $a = [1, 2, 3];',
            "<?php\n\n\$a = [1, 2];",
            ['remove' => 'last'],
        ];

        yield 'It removes every item on by one when set to `all`' => [
            '<?php $a = [1, 2, 3];',
            [
                "<?php\n\n\$a = [2, 3];",
                "<?php\n\n\$a = [1, 3];",
                "<?php\n\n\$a = [1, 2];",
            ],
            ['remove' => 'all'],
        ];

        yield 'It obeys limit when mutating arrays in `all` mode' => [
            '<?php $a = [1, 2, 3];',
            [
                "<?php\n\n\$a = [2, 3];",
                "<?php\n\n\$a = [1, 3];",
            ],
            ['remove' => 'all', 'limit' => 2],
        ];

        yield 'It mutates arrays having required items count when removing `all` items' => [
            '<?php $a = [1, 2];',
            [
                "<?php\n\n\$a = [2];",
                "<?php\n\n\$a = [1];",
            ],
            ['remove' => 'all', 'limit' => 2],
        ];

        yield 'It mutates correctly for limit value (1)' => [
            '<?php $a = [1];',
            [
                "<?php\n\n\$a = [];",
            ],
            ['remove' => 'all', 'limit' => 1],
        ];
    }

    /**
     * @dataProvider provideInvalidConfigurationCases
     */
    public function test_settings_validation(string $setting, $value, string $valueInError): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid configuration of ArrayItemRemoval mutator. Setting `%s` is invalid (%s)',
            $setting,
            $valueInError
        ));
        $this->doTest(
            '<?php $a = [1, 2, 3];',
            "<?php\n\n\$a = [2, 3];",
            [$setting => $value]
        );
    }

    public function provideInvalidConfigurationCases(): Generator
    {
        yield 'remove is null' => [
            'remove', null, '<NULL>',
        ];

        yield 'remove is array' => [
            'remove', [], '<ARRAY>',
        ];

        yield 'remove is not valid' => [
            'remove', 'INVALID', 'invalid',
        ];

        yield 'remove is not string' => [
            'remove', 1, '1',
        ];

        yield 'limit is null' => [
            'limit', null, '<NULL>',
        ];

        yield 'limit is array' => [
            'limit', [], '<ARRAY>',
        ];

        yield 'limit is zero' => [
            'limit', 0, '0',
        ];

        yield 'limit is negative' => [
            'limit', -1, '-1',
        ];

        yield 'limit is not numeric' => [
            'limit', 'INVALID', 'INVALID',
        ];
    }
}
