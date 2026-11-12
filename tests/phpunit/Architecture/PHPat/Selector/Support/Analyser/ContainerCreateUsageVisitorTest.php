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

namespace Infection\Tests\Architecture\PHPat\Selector\Support\Analyser;

use Infection\Testing\SingletonContainer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

#[CoversClass(ContainerCreateUsageVisitor::class)]
final class ContainerCreateUsageVisitorTest extends TestCase
{
    #[DataProvider('codeProvider')]
    public function test_it_detects_container_creation_in_the_target_class(
        string $code,
        bool $expected,
        string $className = 'Example',
    ): void {
        $nodes = SingletonContainer::getContainer()->getParser()->parse($code);
        Assert::notNull($nodes, 'Expected valid PHP code to produce an AST.');

        $visitor = new ContainerCreateUsageVisitor($className);
        $traverser = new NodeTraverser(
            new NameResolver(null, ['replaceNodes' => false]),
            $visitor,
        );
        $traverser->traverse($nodes);

        $this->assertSame($expected, $visitor->callsContainerCreate());
    }

    public static function codeProvider(): iterable
    {
        yield 'imported container' => [
            <<<'PHP'
                <?php
                use Infection\Container\Container;
                class Example {
                    public function create() { return Container::create(); }
                }
                PHP,
            true,
        ];

        yield 'aliased container' => [
            <<<'PHP'
                <?php
                use Infection\Container\Container as ApplicationContainer;
                class Example {
                    public function create() { return ApplicationContainer::create(); }
                }
                PHP,
            true,
        ];

        yield 'fully qualified container with case-insensitive class and method names' => [
            <<<'PHP'
                <?php
                class Example {
                    public function create() { return \infection\container\container::CREATE(); }
                }
                PHP,
            true,
        ];

        yield 'group import in a namespace' => [
            <<<'PHP'
                <?php
                namespace Tests;
                use Infection\Container\{Container as ApplicationContainer};
                class Example {
                    public function create() { return ApplicationContainer::create(); }
                }
                PHP,
            true,
            'Tests\Example',
        ];

        yield 'call inside a closure' => [
            <<<'PHP'
                <?php
                class Example {
                    public function create() { return static fn () => \Infection\Container\Container::create(); }
                }
                PHP,
            true,
        ];

        yield 'first-class callable' => [
            <<<'PHP'
                <?php
                class Example {
                    public function create() { return \Infection\Container\Container::create(...); }
                }
                PHP,
            true,
        ];

        yield 'abstract test helper' => [
            <<<'PHP'
                <?php
                abstract class Example {
                    public function create() { return \Infection\Container\Container::create(); }
                }
                PHP,
            true,
        ];

        yield 'test helper trait' => [
            <<<'PHP'
                <?php
                trait Example {
                    public function create() { return \Infection\Container\Container::create(); }
                }
                PHP,
            true,
        ];

        yield 'call inside a nested anonymous class' => [
            <<<'PHP'
                <?php
                class Example {
                    public function create() {
                        return new class {
                            public function create() { return \Infection\Container\Container::create(); }
                        };
                    }
                }
                PHP,
            true,
        ];

        yield 'singleton container' => [
            <<<'PHP'
                <?php
                use Infection\Testing\SingletonContainer;
                class Example {
                    public function create() { return SingletonContainer::getContainer(); }
                }
                PHP,
            false,
        ];

        yield 'unrelated container with the same short name' => [
            <<<'PHP'
                <?php
                use Other\Container;
                class Example {
                    public function create() { return Container::create(); }
                }
                PHP,
            false,
        ];

        yield 'a different method on the application container' => [
            <<<'PHP'
                <?php
                class Example {
                    public function create() { return \Infection\Container\Container::other(); }
                }
                PHP,
            false,
        ];

        yield 'dynamic class name cannot be identified' => [
            <<<'PHP'
                <?php
                class Example {
                    public function create($className) { return $className::create(); }
                }
                PHP,
            false,
        ];

        yield 'dynamic method name cannot be identified' => [
            <<<'PHP'
                <?php
                class Example {
                    public function create($method) { return \Infection\Container\Container::$method(); }
                }
                PHP,
            false,
        ];

        yield 'calls outside the target class' => [
            <<<'PHP'
                <?php
                \Infection\Container\Container::create();
                class Before {
                    public function create() { return \Infection\Container\Container::create(); }
                }
                class Example {}
                class After {
                    public function create() { return \Infection\Container\Container::create(); }
                }
                PHP,
            false,
        ];

        yield 'source snippets are not executed calls' => [
            <<<'PHP'
                <?php
                class Example {
                    public function code() { return '\Infection\Container\Container::create()'; }
                }
                PHP,
            false,
        ];
    }
}
