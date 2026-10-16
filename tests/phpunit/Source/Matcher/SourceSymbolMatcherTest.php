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

namespace Infection\Tests\Source\Matcher;

use Infection\Configuration\SourceSymbol\SourceSymbolSelector;
use Infection\Source\Matcher\SourceSymbolMatcher;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceSymbolMatcher::class)]
final class SourceSymbolMatcherTest extends TestCase
{
    #[DataProvider('coordinateProvider')]
    public function test_it_matches_method_and_line_coordinates(
        SourceSymbolSelector $selector,
        bool $expected,
    ): void {
        $class = new Class_('Mailer');
        $class->namespacedName = new Name('App\\Mailer');
        $matcher = new SourceSymbolMatcher([$selector]);
        $node = new Int_(
            1,
            ['startLine' => 10, 'endLine' => 12],
        );

        $this->assertSame(
            $expected,
            $matcher->matches(
                $node,
                $class,
                new ClassMethod('send'),
            ),
            'Method and absolute line constraints must both match within the selected qualified class.',
        );
    }

    public static function coordinateProvider(): iterable
    {
        yield 'line before the node' => [
            new SourceSymbolSelector(
                'App\\Mailer',
                'send',
                9,
            ),
            false,
        ];

        yield 'first line of the node' => [
            new SourceSymbolSelector(
                'App\\Mailer',
                'send',
                10,
            ),
            true,
        ];

        yield 'line inside the node' => [
            new SourceSymbolSelector(
                'App\\Mailer',
                'send',
                11,
            ),
            true,
        ];

        yield 'last line of the node' => [
            new SourceSymbolSelector(
                'App\\Mailer',
                'send',
                12,
            ),
            true,
        ];

        yield 'line after the node' => [
            new SourceSymbolSelector(
                'App\\Mailer',
                'send',
                13,
            ),
            false,
        ];

        yield 'class line without method constraint' => [
            new SourceSymbolSelector(
                'App\\Mailer',
                null,
                11,
            ),
            true,
        ];

        yield 'other method on matching line' => [
            new SourceSymbolSelector(
                'App\\Mailer',
                'receive',
                11,
            ),
            false,
        ];

        yield 'same method in another qualified class' => [
            new SourceSymbolSelector(
                'Other\\Mailer',
                'send',
                11,
            ),
            false,
        ];

        yield 'case insensitive method' => [
            new SourceSymbolSelector(
                'App\\Mailer',
                'SeNd',
                11,
            ),
            true,
        ];
    }

    #[DataProvider('classNameProvider')]
    public function test_it_matches_class_names(
        string $selector,
        string $className,
        bool $expected,
    ): void {
        $class = new Class_('Mailer');
        $class->namespacedName = new Name($className);
        $matcher = new SourceSymbolMatcher([
            new SourceSymbolSelector(
                $selector,
                null,
                null,
            ),
        ]);

        $this->assertSame(
            $expected,
            $matcher->matches(
                $class,
                $class,
                null,
            ),
            'Class matching must distinguish short names from explicitly qualified names.',
        );
    }

    public static function classNameProvider(): iterable
    {
        yield 'short name matches global class' => [
            'Mailer',
            'Mailer',
            true,
        ];

        yield 'short name matches namespaced class' => [
            'Mailer',
            'App\Mailer',
            true,
        ];

        yield 'explicit global name matches global class' => [
            '\Mailer',
            'Mailer',
            true,
        ];

        yield 'explicit global name excludes namespaced class' => [
            '\Mailer',
            'App\Mailer',
            false,
        ];

        yield 'qualified name matches namespaced class' => [
            'App\Mailer',
            'App\Mailer',
            true,
        ];

        yield 'explicitly qualified name matches namespaced class' => [
            '\App\Mailer',
            'App\Mailer',
            true,
        ];

        yield 'explicitly qualified name excludes another namespace' => [
            '\App\Mailer',
            'Other\Mailer',
            false,
        ];

        yield 'explicit global name remains case insensitive' => [
            '\mAiLeR',
            'Mailer',
            true,
        ];
    }
}
