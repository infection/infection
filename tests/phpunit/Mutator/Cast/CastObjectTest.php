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

namespace Infection\Tests\Mutator\Cast;

use Infection\Mutator\Cast\CastObject;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CastObject::class)]
final class CastObjectTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It removes casting to object' => [
            <<<'PHP'
                <?php

                (object) ['test' => 1];
                PHP
            ,
            <<<'PHP'
                <?php

                ['test' => 1];
                PHP
            ,
        ];

        yield 'It removes casting to object in conditions' => [
            <<<'PHP'
                <?php

                if ((object) implode()) {
                    echo 'Hello';
                }
                PHP
            ,
            <<<'PHP'
                <?php

                if (implode()) {
                    echo 'Hello';
                }
                PHP
            ,
        ];

        yield 'It removes casting to object in global return' => [
            <<<'PHP'
                <?php

                return (object) implode();
                PHP
            ,
            <<<'PHP'
                <?php

                return implode();
                PHP
            ,
        ];

        yield 'It removes casting to object in return of untyped-function' => [
            <<<'PHP'
                <?php

                function noReturnType()
                {
                    return (object) implode();
                }
                PHP,
            <<<'PHP'
                <?php

                function noReturnType()
                {
                    return implode();
                }
                PHP,
        ];

        yield 'It removes casting to object in return of object-function when strict-types=0' => [
            <<<'PHP'
                <?php

                declare (strict_types=0);
                function returnsObject(): object
                {
                    return (object) implode();
                }
                PHP,
            <<<'PHP'
                <?php

                declare (strict_types=0);
                function returnsObject(): object
                {
                    return implode();
                }
                PHP,
        ];

        yield 'It not removes casting to object in return of object-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsObject(): object {
                    return (object) implode();
                }
                PHP,
        ];

        yield 'It not removes casting to object in nested return of object-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsObject(): object {
                    if (true) {
                        return (object) implode();
                    }
                    return new stdClass();
                }
                PHP,
        ];
    }
}
