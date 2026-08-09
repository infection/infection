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

namespace Infection\Tests\Architecture\PHPat\Selector;

use Infection\CannotBeInstantiated;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\VersionParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ClassNamedAny::class)]
final class ClassNamedAnyTest extends SelectorTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_matches_classes_named_in_the_list(
        string $className,
        bool $expected,
    ): void {
        $selector = new ClassNamedAny([
            CannotBeInstantiated::class,
            CommandLineBuilder::class,
        ]);
        $classReflection = $this->createClassReflection($className);

        $actual = $selector->matches($classReflection);

        $this->assertSame($expected, $actual);
    }

    public function test_it_exposes_the_selected_class_names_as_name(): void
    {
        $selector = new ClassNamedAny([
            CannotBeInstantiated::class,
            CommandLineBuilder::class,
        ]);

        $actual = $selector->getName();

        $this->assertSame(
            'Infection\CannotBeInstantiated, Infection\TestFramework\Common\CommandLineBuilder',
            $actual,
        );
    }

    public static function classProvider(): iterable
    {
        yield 'first selected class' => [
            CannotBeInstantiated::class,
            true,
        ];

        yield 'second selected class' => [
            CommandLineBuilder::class,
            true,
        ];

        yield 'unselected class' => [
            VersionParser::class,
            false,
        ];
    }
}
