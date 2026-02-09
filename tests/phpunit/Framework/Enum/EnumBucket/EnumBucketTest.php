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

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2021 Webmozarts GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Infection\Tests\Framework\Enum\EnumBucket;

use BackedEnum;
use Exception;
use Infection\Framework\Enum\EnumBucket;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(EnumBucket::class)]
final class EnumBucketTest extends TestCase
{
    /**
     * @param class-string<BackedEnum> $enumClassName
     * @param list<BackedEnum> $expected
     */
    #[DataProvider('enumProvider')]
    public function test_it_can_be_instantiated(
        string $enumClassName,
        array $expected,
    ): void {
        $actual = EnumBucket::create($enumClassName)->takeAll();

        $this->assertSame($expected, $actual);
    }

    public static function enumProvider(): iterable
    {
        yield 'backed enum' => [
            StringBackedEnum::class,
            [
                StringBackedEnum::ALPHA,
                StringBackedEnum::BETA,
                StringBackedEnum::GAMMA,
            ],
        ];
    }

    /**
     * @param class-string<BackedEnum> $enumClassName
     */
    #[DataProvider('enumProvider')]
    public function test_taking_all_values_removes_values_from_the_bucket(
        string $enumClassName,
    ): void {
        $bucket = EnumBucket::create($enumClassName);

        $bucket->takeAll();
        $actual = $bucket->takeAll();

        $this->assertSame([], $actual);
    }

    /**
     * @param class-string<BackedEnum> $enumClassName
     */
    #[DataProvider('enumProvider')]
    public function test_it_is_empty_after_taking_all_values(
        string $enumClassName,
    ): void {
        $bucket = EnumBucket::create($enumClassName);

        $bucket->takeAll();

        $this->assertTrue($bucket->isEmpty());
    }

    public function test_it_can_take_values_of_a_backed_enum(): void
    {
        $bucket = EnumBucket::create(StringBackedEnum::class);

        $this->assertFalse($bucket->isEmpty());
        $this->assertSame(StringBackedEnum::ALPHA, $bucket->take(StringBackedEnum::ALPHA));
        $this->assertSame(StringBackedEnum::BETA, $bucket->take(StringBackedEnum::BETA));
        $this->assertSame([StringBackedEnum::GAMMA], $bucket->takeAll());
        $this->assertTrue($bucket->isEmpty());

        // No exception thrown
        $bucket->assertIsEmpty();
    }

    public function test_it_fails_to_assert_that_the_bucket_with_a_helpful_error_message(): void
    {
        $bucket = EnumBucket::create(StringBackedEnum::class);
        $bucket->take(StringBackedEnum::ALPHA);

        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Expected the bucket to be empty. The following case(s) were found: "(name=BETA,value=beta)", "(name=GAMMA,value=gamma)".',
            ),
        );

        $bucket->assertIsEmpty();
    }

    /** @phpstan-ignore missingType.generics */
    #[DataProvider('nonExistentValueProvider')]
    public function test_it_cannot_take_values_which_are_not_available_in_the_enum(
        EnumBucket $bucket,
        mixed $value,
        Exception $expected,
    ): void {
        $this->expectExceptionObject($expected);

        $bucket->take($value);
    }

    public static function nonExistentValueProvider(): iterable
    {
        yield 'non-enum value of a bucket of a backed enum' => [
            EnumBucket::create(StringBackedEnum::class),
            'unknown',
            new InvalidArgumentException(
                sprintf(
                    'Expected value "string" to be a case of the enum "%s".',
                    StringBackedEnum::class,
                ),
            ),
        ];

        yield 'backed enum value of a bucket of another backed enum' => [
            EnumBucket::create(StringBackedEnum::class),
            AnotherStringBackedEnum::ALPHA,
            new InvalidArgumentException(
                sprintf(
                    'The enum "%s" does not have a case "(name=ALPHA,value=α)". Known names are: "(name=ALPHA,value=alpha)',
                    StringBackedEnum::class,
                ),
            ),
        ];
    }

    /** @phpstan-ignore missingType.generics */
    /** @phpstan-ignore missingType.generics */
    #[DataProvider('noLongerAvailableValueProvider')]
    public function test_it_cannot_take_a_value_from_the_bucket_twice(
        EnumBucket $bucket,
        mixed $value,
        Exception $expected,
    ): void {
        $bucket->take($value);

        $this->expectExceptionObject($expected);

        $bucket->take($value);
    }

    public static function noLongerAvailableValueProvider(): iterable
    {
        yield 'backed enum' => [
            EnumBucket::create(StringBackedEnum::class),
            StringBackedEnum::ALPHA,
            new OutOfBoundsException(
                'The case "(name=ALPHA,value=alpha)" is no longer available in the bucket. Available cases are: "(name=BETA,value=beta)", "(name=GAMMA,value=gamma)".',
            ),
        ];
    }
}
