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

use Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(ChangingIgnorer::class)]
final class ChangingIgnorerTest extends BaseNodeIgnorerTestCase
{
    private const CODE_WITH_ONE_IGNORED_NODE = <<<'PHP'
        <?php

        class Foo
        {
            public function bar()
            {
                $ignored + 1;
            }
        }

        PHP;

    private const CODE_WITH_ONE_COUNTED_NODE = <<<'PHP'
        <?php

        class Foo
        {
            public function bar()
            {
                $counted + 1;
            }
        }

        PHP;

    public function test_it_ignores_when_enabled(): ChangingIgnorer
    {
        $ignorer = new ChangingIgnorer();
        $ignorer->startIgnoring();

        $this->parseAndTraverse(
            self::CODE_WITH_ONE_IGNORED_NODE,
            $spy = $this->createSpy(),
            $ignorer,
        );

        $this->assertSame(0, $spy->nodeCounter);

        return $ignorer;
    }

    #[Depends('test_it_ignores_when_enabled')]
    public function test_it_does_not_ignore_when_disabled(ChangingIgnorer $ignorer): void
    {
        $ignorer->stopIgnoring();

        $this->parseAndTraverse(
            self::CODE_WITH_ONE_COUNTED_NODE,
            $spy = $this->createSpy(),
            $ignorer,
        );

        $this->assertSame(1, $spy->nodeCounter);
    }

    protected function getIgnore(): NodeIgnorer
    {
        return new ChangingIgnorer();
    }
}
