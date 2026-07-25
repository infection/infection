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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

#[CoversClass(EnvironmentVariableUsageVisitor::class)]
final class EnvironmentVariableUsageVisitorTest extends TestCase
{
    /**
     * @param list<string> $expected
     */
    #[DataProvider('codeProvider')]
    public function test_it_finds_statically_identifiable_environment_variables(
        string $code,
        array $expected,
    ): void {
        $nodes = SingletonContainer::getContainer()->getParser()->parse($code);
        Assert::notNull($nodes);

        $visitor = new EnvironmentVariableUsageVisitor();
        (new NodeTraverser($visitor))->traverse($nodes);

        $actual = $visitor->getEnvironmentVariables();

        $this->assertSame($expected, $actual);
    }

    public static function codeProvider(): iterable
    {
        yield 'putenv assignment' => [
            <<<'PHP'
                <?php

                putenv('FOO=bar');
                PHP,
            ['FOO'],
        ];

        yield 'putenv removal' => [
            <<<'PHP'
                <?php

                putenv('FOO');
                PHP,
            ['FOO'],
        ];

        yield 'Safe putenv' => [
            <<<'PHP'
                <?php

                \Safe\putenv('FOO=bar');
                PHP,
            ['FOO'],
        ];

        yield 'putenv with a dynamic value' => [
            <<<'PHP'
                <?php

                putenv('FOO=' . $value);
                PHP,
            ['FOO'],
        ];

        yield 'putenv with a dynamic name' => [
            <<<'PHP'
                <?php

                putenv($name . '=bar');
                PHP,
            [],
        ];

        yield '_ENV access' => [
            <<<'PHP'
                <?php

                $value = $_ENV['FOO'];
                PHP,
            ['FOO'],
        ];

        yield '_ENV access with a dynamic name' => [
            <<<'PHP'
                <?php

                $value = $_ENV[$name];
                PHP,
            [],
        ];

        yield '_SERVER access' => [
            <<<'PHP'
                <?php

                $value = $_SERVER['FOO'];
                PHP,
            [],
        ];

        yield '_SERVER access with a dynamic name' => [
            <<<'PHP'
                <?php

                $value = $_SERVER[$name];
                PHP,
            [],
        ];

        yield 'getenv call' => [
            <<<'PHP'
                <?php

                getenv('FOO');
                PHP,
            [],
        ];

        yield 'duplicates' => [
            <<<'PHP'
                <?php

                putenv('FOO=bar');
                $_ENV['FOO'] = 'bar';
                PHP,
            ['FOO'],
        ];
    }
}
