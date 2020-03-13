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

use PHPUnit\Framework\TestCase;

final class PHPDocParserTest extends TestCase
{
    /**
     * @dataProvider phpDocProvider
     */
    public function test_it_can_parse_phpdoc(string $phpDoc, array $expected): void
    {
        $actual = (new PHPDocParser())->parse($phpDoc);

        $this->assertSame($expected, $actual);
    }

    public function phpDocProvider(): iterable
    {
        yield 'empty' => [
            '',
            [],
        ];

        yield 'summary' => [
            <<<'DOCBLOCK'
This is a multiline
description that you commonly
see with tags.
    It does have a multiline code sample
    that should align, no matter what
All spaces superfluous spaces on the
second and later lines should be
removed but the code sample should
still be indented.
DOCBLOCK
            ,
            [],
        ];

        yield 'simple tag' => [
            '@see',
            ['@see'],
        ];

        yield 'simple tag tag comment' => [
            '@see https://infection.org',
            ['@see'],
        ];

        yield 'email tag' => [
            '@author theo.fidry@gmail.com',
            ['@author'],
        ];

        yield 'doctrine annotation' => [
            <<<'DOCBLOCK'
@var \DateTime[]
@Groups({"a", "b"})
@ORM\Entity
DOCBLOCK
            ,
            ['@var', '@Groups', '@ORM\Entity'],
        ];

        yield 'summary with ellipsis' => [
            <<<'DOCBLOCK'
 This is a short (...) description.

 This is a long description.

 @return void
DOCBLOCK
            ,
            ['@return'],
        ];

        yield 'duplicate tags' => [
            <<<'DOCBLOCK'
@final
@final
@internal
DOCBLOCK
            ,
            ['@final', '@internal'],
        ];

        yield 'summary with escaped phpdoc' => [
            <<<'DOCBLOCK'
You can escape the @-sign by surrounding it with braces, for example: @. And escape a closing brace within an
inline tag by adding an opening brace in front of it like this: }.
Here are example texts where you can see how they could be used in a real life situation:
    This is a text with an {@internal inline tag where a closing brace (}) is shown}.
    Or an {@internal inline tag with a literal {@link} in it}.
Do note that an {@internal inline tag that has an opening brace ({) does not break out}.
DOCBLOCK
            ,
            ['@internal', '@link'],
        ];
    }
}
