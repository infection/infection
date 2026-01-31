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

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalNot;
use Infection\Testing\BaseMutatorTestCase;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LogicalNot::class)]
final class LogicalNotTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[]|null $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array|null $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It removes logical not' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return !false;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    return false;
                    PHP,
            ),
        ];

        yield 'It does not remove double logical not' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    return !!false;
                    PHP,
            ),
        ];

        yield 'It does not remove negation on match() to prevent overlap with MatchArmRemoval' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $matched = !match ($potionItem->getTypeId()){
                        ItemTypeIds::POTION, ItemTypeIds::LINGERING_POTION => true,
                        default => false,
                    };
                    PHP,
            ),
        ];
    }

    public function test_it_mutates_logical_not(): void
    {
        $expr = new BooleanNot(new ConstFetch(new Name('false')));

        $this->assertTrue($this->mutator->canMutate($expr));
    }

    public function test_it_does_not_mutates_doubled_logical_not(): void
    {
        $expr = new BooleanNot(
            new BooleanNot(new ConstFetch(new Name('false'))),
        );

        $this->assertFalse($this->mutator->canMutate($expr));
    }
}
