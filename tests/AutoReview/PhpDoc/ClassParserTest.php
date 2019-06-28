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

namespace Infection\Tests\AutoReview\PhpDoc;

use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Infection\Tests\AutoReview\PhpDoc\ClassParser
 *
 * @group autoReview
 */
final class ClassParserTest extends TestCase
{
    /**
     * @dataProvider contentsProvider
     *
     * @param string[] $expected
     */
    public function test_it_can_parse_phpdocs(string $contents, array $expected): void
    {
        $actual = ClassParser::parseFilePhpDoc($contents);

        $this->assertSame($expected, $actual);
    }

    public function contentsProvider(): Generator
    {
        yield ['', []];

        yield [
            <<<'PHP'
<?php

PHP
            ,
            [],
        ];

        yield [
            <<<'PHP'
<?php

class Foo {}
PHP
            ,
            [],
        ];

        yield [
            <<<'PHP'
<?php declare(strict_types=1);

/**
 * License
 */

namespace Acme;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @group fixtures
 *
 * @author Théo Fidry <theo.fidry@gmail.com>
 */
final class FooTest extends TestCase {}

PHP
            ,
            [
                '@coversNothing' => null,
                '@group' => 'fixtures',
                '@author' => 'Théo Fidry <theo.fidry@gmail.com>',
            ],
        ];

        yield [
            <<<'PHP'
<?php declare(strict_types=1);

/**
 * @license
 */

namespace Acme;

use PHPUnit\Framework\TestCase;

/** @coversNothing */
final class FooTest extends TestCase {}
PHP
            ,
            ['@coversNothing' => null],
        ];

        yield [
            <<<'PHP'
<?php declare(strict_types=1);

/**
 * @license MIT
 */

final class FooTest extends TestCase {}

PHP
            ,
            [],
        ];

        yield [
            <<<'PHP'
<?php declare(strict_types=1);

/**
 * @foo
 */
final class FooTest extends TestCase {}

/**
 * @bar
 */
final class BarTest extends TestCase {}

PHP
            ,
            ['@foo' => null],
        ];

        yield [
            <<<'PHP'
<?php declare(strict_types=1);

/**
 * @foo
 */
abstract class FooTest {}

PHP
            ,
            ['@foo' => null],
        ];

        yield [
            <<<'PHP'
<?php declare(strict_types=1);

/**
 * @foo
 */
abstract class FooTest {
    /**
     * @dataProvider voidProvider
     */
    function test_something(): void {
        // ...
    }
}

PHP
            ,
            ['@foo' => null],
        ];

        yield [
            <<<'PHP'
<?php declare(strict_types=1);

/**
 * @foo
 */
abstract class FooTest {
    /**
     * @dataProvider voidProvider
     */
    function test_something(): void {
        // hello class something
    }
}

PHP
            ,
            ['@foo' => null],
        ];
    }
}
