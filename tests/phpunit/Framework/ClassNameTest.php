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

namespace Infection\Tests\Framework;

use Closure;
use Exception;
use Infection\Console\Application;
use Infection\Framework\ClassName;
use Infection\Framework\Enum\EnumBucket;
use Infection\Tests\Framework\Enum\EnumBucket\EnumBucketTest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassName::class)]
final class ClassNameTest extends TestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classNameProvider')]
    public function test_it_can_get_a_class_short_name(
        string $className,
        string $expectedShortName,
    ): void {
        $actual = ClassName::getShortClassName($className);

        $this->assertSame($expectedShortName, $actual);
    }

    public static function classNameProvider(): iterable
    {
        yield 'nominal' => [
            ClassName::class,
            'ClassName',
        ];

        yield 'UTF-8' => [
            'Webmozarts\ClassName\Ütf8',
            'Ütf8',
        ];

        yield 'emoji' => [
            'Webmozarts\ClassName\Special😋Class',
            'Special😋Class',
        ];

        yield 'root namespace' => [
            Closure::class,
            'Closure',
        ];
    }

    /**
     * @param class-string $sourceClassName
     * @param Exception|class-string[] $expected
     */
    #[DataProvider('sourceClassNamesProvider')]
    public function test_it_gives_the_canonical_test_class_names_for_a_source_class(
        string $sourceClassName,
        array|Exception $expected,
    ): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = ClassName::getCanonicalTestClassNames($sourceClassName);

        if (!($expected instanceof Exception)) {
            $this->assertSame($expected, $actual);
        }
    }

    public static function sourceClassNamesProvider(): iterable
    {
        yield 'nominal' => [
            Application::class,
            [
                'Infection\Tests\Console\ApplicationTest',
                'Infection\Tests\Console\Application\ApplicationTest',
            ],
        ];

        yield 'source with "Infection" mentioned multiple times' => [
            'Infection\Framework\Infection\InfectionReport',
            [
                'Infection\Tests\Framework\Infection\InfectionReportTest',
                'Infection\Tests\Framework\Infection\InfectionReport\InfectionReportTest',
            ],
        ];

        yield 'source located in tests' => [
            'Infection\Tests\Console\Application',
            [
                'Infection\Tests\Console\ApplicationTest',
                'Infection\Tests\Console\Application\ApplicationTest',
            ],
        ];

        // This is most likely incorrect, but there is no case for it neither
        // is it clear that it is a case want to support.
        // Nonetheless, we have this test case to pin this scenario.
        yield 'already a test' => [
            'Infection\Tests\Console\ApplicationTest',
            [
                'Infection\Tests\Console\ApplicationTestTest',
                'Infection\Tests\Console\ApplicationTest\ApplicationTestTest',
            ],
        ];

        yield 'third-party code' => [
            \Symfony\Component\Console\Application::class,
            new InvalidArgumentException(
                'Expected source fully-qualified class name to be a source file from Infection. Got "Symfony\Component\Console\Application".',
            ),
        ];
    }

    /**
     * @param class-string $sourceClassName
     * @param class-string|string $expected
     */
    #[DataProvider('sourceClassNameProvider')]
    public function test_it_gives_the_canonical_test_class_name(
        string $sourceClassName,
        ?string $expected,
    ): void {
        $actual = ClassName::getCanonicalTestClassName($sourceClassName);

        $this->assertSame($expected, $actual);
    }

    public static function sourceClassNameProvider(): iterable
    {
        // !! Beware !!
        // For the sake of keeping this test simple, we use real classes.
        // This means this test may break due to unrelated refactorings.
        // It should, however, be trivial to update.

        yield 'matching test case is the first candidate' => [
            ClassName::class,
            self::class,
        ];

        yield 'matching test case is the second candidate' => [
            EnumBucket::class,
            EnumBucketTest::class,
        ];

        yield 'non-existent test case' => [
            'Infection\Unknown\UnknownClass',
            null,
        ];
    }

    /**
     * @param class-string $testClassName
     * @param Exception|class-string[] $expected
     */
    #[DataProvider('notTestClassNamesProvider')]
    public function test_it_gives_the_canonical_source_class_names_for_a_test_class(
        string $testClassName,
        array|Exception $expected,
    ): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = ClassName::getCanonicalSourceClassNames($testClassName);

        if (!($expected instanceof Exception)) {
            $this->assertSame($expected, $actual);
        }
    }

    public static function notTestClassNamesProvider(): iterable
    {
        yield 'non-ambiguous case' => [
            'Infection\Tests\Console\ApplicationTest',
            [Application::class],
        ];

        yield '2nd test canonical form' => [
            'Infection\Tests\Console\Application\ApplicationTest',
            [
                Application::class,
                'Infection\Console\Application\Application',
            ],
        ];

        yield 'test located in source' => [
            'Infection\Console\ApplicationTest',
            [Application::class],
        ];

        // This is most likely incorrect, but there is no case for it neither
        // is it clear that it is a case want to support.
        // Nonetheless, we have this test case to pin this scenario.
        yield 'not a test' => [
            'Infection\Tests\Console\Application',
            new InvalidArgumentException(
                'Expected test fully-qualified class name to follow the PHPUnit test naming convention, i.e. to have the suffix "Test". Got "Infection\Tests\Console\Application".',
            ),
        ];

        yield 'third-party code' => [
            'Symfony\Component\Console\ApplicationTest',
            new InvalidArgumentException(
                'Expected test fully-qualified class name to be a test file from Infection. Got "Symfony\Component\Console\ApplicationTest"',
            ),
        ];
    }

    /**
     * @param class-string $testClassName
     * @param class-string|string $expected
     */
    #[DataProvider('notTestClassNameProvider')]
    public function test_it_gives_the_canonical_source_class_name(
        string $testClassName,
        ?string $expected,
    ): void {
        $actual = ClassName::getCanonicalSourceClassName($testClassName);

        $this->assertSame($expected, $actual);
    }

    public static function notTestClassNameProvider(): iterable
    {
        // !! Beware !!
        // For the sake of keeping this test simple, we use real classes.
        // This means this test may break due to unrelated refactorings.
        // It should, however, be trivial to update.

        yield 'matching test case is the first candidate' => [
            self::class,
            ClassName::class,
        ];

        yield 'matching test case is the second candidate' => [
            EnumBucketTest::class,
            EnumBucket::class,
        ];

        yield 'non-existent test case' => [
            'Infection\Tests\Unknown\UnknownClassTest',
            null,
        ];
    }
}
