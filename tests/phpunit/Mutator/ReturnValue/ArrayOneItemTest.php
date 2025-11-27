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

namespace Infection\Tests\Mutator\ReturnValue;

use Composer\InstalledVersions;
use Infection\Mutator\ReturnValue\ArrayOneItem;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Tests\Mutator\MutatorFixturesProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use function version_compare;

#[Group('integration')]
#[CoversClass(ArrayOneItem::class)]
final class ArrayOneItemTest extends BaseMutatorTestCase
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
        // PHP-Parser 5.0 brings inconsistent parens behaviour for ternary operator and this issue was fixed in PHP-Parser 5.1
        // @see https://github.com/nikic/PHP-Parser/issues/1009
        if (version_compare((string) InstalledVersions::getPrettyVersion('nikic/php-parser'), 'v5.1', '<')) {
            $expected = <<<'PHP'
                <?php

                namespace ArrayOneItem_NotNullableArray;

                class Test
                {
                    public function getCollection(): array
                    {
                        $collection = [1, 2, 3];
                        return (count($collection) > 1) ? array_slice($collection, 0, 1, true) : $collection;
                    }
                }
                PHP;
        } else {
            $expected = <<<'PHP'
                <?php

                namespace ArrayOneItem_NotNullableArray;

                class Test
                {
                    public function getCollection(): array
                    {
                        $collection = [1, 2, 3];

                        return count($collection) > 1 ? array_slice($collection, 0, 1, true) : $collection;
                    }
                }

                PHP;
        }

        yield 'It mutates when return typehint is not nullable array' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'mutates-not-nullable-array.php'),
            $expected,
        ];

        yield 'It does not mutate the method call' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'does-not-mutate-method-call.php'),
        ];

        yield 'It does not mutate the function call' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'does-not-mutate-function-call.php'),
        ];

        yield 'It does not mutate the function variable call' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'does-not-mutate-function-variable-call.php'),
        ];

        yield 'It does not mutate when raw array is returned' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'does-not-mutate-raw-array.php'),
        ];

        yield 'It does not mutate when return typehint is nullable array' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'does-not-mutate-nullable-array.php'),
        ];

        yield 'It does not mutate when return typehint is not an array' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'does-not-mutate-not-array.php'),
        ];
    }
}
