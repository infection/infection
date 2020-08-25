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

namespace Infection\Tests\PhpParser\Visitor\IgnoreNode;

use Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use Infection\PhpParser\Visitor\IgnoreNode\PhpUnitCodeCoverageAnnotationIgnorer;

final class PhpUnitCodeCoverageAnnotationIgnorerTest extends BaseNodeIgnorerTestCase
{
    /**
     * @dataProvider provideIgnoreCases
     */
    public function test_it_ignores_the_right_nodes(string $code, int $count): void
    {
        $spy = $this->createSpy();

        $this->parseAndTraverse($code, $spy);

        $this->assertSame($count, $spy->nodeCounter);
    }

    public function provideIgnoreCases(): iterable
    {
        yield 'classes with annotation are ignored' => [
            <<<'PHP'
<?php

/**
 * @codeCoverageIgnore
 */
class IgnoredClass
{
    public function foo()
    {
        $ignored = true;
    }
}
PHP
            ,
            0,
        ];

        yield 'method with annotations are ignored' => [
            <<<'PHP'
<?php

class IgnoredClass
{
    /**
     * @codeCoverageIgnore
     */
    public function foo()
    {
        $ignored = true;
    }

    public function bar(): void
    {
        $counted = 1;
    }

}
PHP
            ,
            1,
        ];

        yield 'methods without comments are not ignored' => [
            <<<'PHP'
<?php

class IgnoredClass
{
    public function foo($counted)
    {
        $counted = true;
    }
}
PHP
            ,
            2,
        ];

        yield 'classes without ignore annotation are not ignored' => [
            <<<'PHP'
<?php

/**
 * A comment, but not one that ignores
 */
class Foo
{
    public function bar($counted)
    {
        $counted = 2;
    }
}
PHP
            ,
            2,
        ];

        yield 'methods without ignore annotation are not ignored' => [
            <<<'PHP'
<?php


class Foo
{
    /**
     * A comment, but not one that ignores
     */
    public function bar($counted)
    {
        $counted = 2;
    }
}
PHP
            ,
            2,
        ];
    }

    protected function getIgnore(): NodeIgnorer
    {
        return new PhpUnitCodeCoverageAnnotationIgnorer();
    }
}
