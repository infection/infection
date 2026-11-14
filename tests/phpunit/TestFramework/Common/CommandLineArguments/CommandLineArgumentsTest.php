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

namespace Infection\Tests\TestFramework\Common\CommandLineArguments;

use Infection\TestFramework\Common\CommandLineArguments\CommandLineArguments;
use Infection\TestFramework\Common\CommandLineArguments\TestFrameworkExtraArgs;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;

#[CoversClass(CommandLineArguments::class)]
#[CoversClass(TestFrameworkExtraArgs::class)]
final class CommandLineArgumentsTest extends TestCase
{
    /**
     * @param list<string> $expected
     */
    #[DataProvider('removalProvider')]
    public function test_it_removes_options_without_changing_other_arguments(string $input, array $expected): void
    {
        $arguments = CommandLineArguments::fromString($input);
        $original = $arguments->toTokens();
        $filtered = $arguments->withoutOptions(
            new InputOption('filter', 'f|F', InputOption::VALUE_REQUIRED),
            new InputOption('initial-only'),
        );

        $this->assertSame($expected, $filtered->toTokens());
        $this->assertSame($original, $arguments->toTokens());
    }

    public static function removalProvider(): iterable
    {
        yield 'empty input' => ['', []];

        yield 'separate value' => ['--filter "Some test" --colors=always', ['--colors=always']];

        yield 'attached value' => ['--filter="Some test" tests/Foo.php', ['tests/Foo.php']];

        yield 'empty attached value does not consume a path' => ['--filter= tests/Foo.php', ['tests/Foo.php']];

        yield 'empty separate value' => ['--filter "" tests/Foo.php', ['tests/Foo.php']];

        yield 'short alias' => ['-f "Some test" --colors', ['--colors']];

        yield 'second short alias' => ['-F SomeTest', []];

        yield 'repeated options and flags' => ['--filter A --initial-only --filter B --initial-only', []];

        yield 'a flag does not consume a path' => ['--initial-only tests/Foo.php', ['tests/Foo.php']];

        yield 'whitespace in values is preserved' => ['--label="a  b" --filter A', ['--label=a  b']];

        yield 'a flag inside another value is preserved' => ['--label="a --initial-only b"', ['--label=a --initial-only b']];

        yield 'an option inside another value is preserved' => ['--label=--filter=A', ['--label=--filter=A']];

        yield 'similar names are preserved' => ['--exclude-filter=A --filter-extra=B', ['--exclude-filter=A', '--filter-extra=B']];

        yield 'arguments after the terminator are preserved' => ['--initial-only -- --filter A --initial-only', ['--', '--filter', 'A', '--initial-only']];

        yield 'a required value may look like an option' => ['--filter --initial-only tests/Foo.php', ['tests/Foo.php']];

        yield 'a required value may equal the terminator' => ['--filter -- --initial-only', []];

        yield 'tabs separate arguments' => ["--initial-only\ttests/Foo.php", ['tests/Foo.php']];
    }

    #[DataProvider('presenceProvider')]
    public function test_it_recognizes_option_occurrences(string $input, bool $expected): void
    {
        $arguments = CommandLineArguments::fromString($input);

        $this->assertSame(
            $expected,
            $arguments->hasOption(new InputOption('filter', 'f', InputOption::VALUE_REQUIRED)),
        );
    }

    public static function presenceProvider(): iterable
    {
        yield 'separate value' => ['--filter A', true];

        yield 'attached value' => ['--filter=A', true];

        yield 'alias' => ['-f A', true];

        yield 'empty input' => ['', false];

        yield 'similar name' => ['--exclude-filter=A', false];

        yield 'option-looking value' => ['--label=--filter=A', false];

        yield 'after terminator' => ['-- --filter A', false];
    }

    public function test_it_rejects_missing_required_values(): void
    {
        $arguments = CommandLineArguments::fromTokens(['--filter']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The option "--filter" requires a value.');

        $arguments->withoutOptions(new InputOption('filter', null, InputOption::VALUE_REQUIRED));
    }

    /**
     * @param InputOption::VALUE_OPTIONAL|InputOption::VALUE_NEGATABLE $mode
     */
    #[DataProvider('unsupportedModeProvider')]
    public function test_it_rejects_unsupported_option_definitions(int $mode): void
    {
        $arguments = CommandLineArguments::fromTokens([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument transformations require required-value or value-less option definitions.');

        $arguments->hasOption(new InputOption('option', null, $mode));
    }

    public static function unsupportedModeProvider(): iterable
    {
        yield 'optional value' => [InputOption::VALUE_OPTIONAL];

        yield 'negatable flag' => [InputOption::VALUE_NEGATABLE];
    }

    /**
     * @param list<string> $tokens
     * @param list<string> $expected
     */
    #[DataProvider('appendProvider')]
    public function test_it_adds_generated_options_before_the_terminator(array $tokens, array $expected): void
    {
        $arguments = CommandLineArguments::fromTokens($tokens);
        $generated = $arguments->append(['--log-junit', '/path with spaces/junit.xml']);

        $this->assertSame($expected, $generated->toTokens());
        $this->assertSame($tokens, $arguments->toTokens());
    }

    public static function appendProvider(): iterable
    {
        yield 'no terminator' => [
            ['--colors'],
            ['--colors', '--log-junit', '/path with spaces/junit.xml'],
        ];

        yield 'with a terminator' => [
            ['--colors', '--', 'tests/Foo.php', '--'],
            ['--colors', '--log-junit', '/path with spaces/junit.xml', '--', 'tests/Foo.php', '--'],
        ];
    }

    public function test_it_distinguishes_a_known_option_value_from_the_terminator(): void
    {
        $arguments = CommandLineArguments::fromTokens(['--filter', '--', '--', 'tests/Foo.php'])
            ->withOptionDefinitions(new InputOption('filter', null, InputOption::VALUE_REQUIRED))
        ;
        $generated = $arguments->append(['--log-junit', '/tmp/junit.xml']);

        $this->assertSame(['--filter', '--', '--log-junit', '/tmp/junit.xml', '--', 'tests/Foo.php'], $generated->toTokens());
    }

    public function test_it_serializes_legacy_arguments_without_losing_values(): void
    {
        $tokens = ['', '0', '--filter=a  b', '--label=--filter=A', 'a"b', "Jenny's", 'C:\\path\\file.php', "a\tb\nc", '--', '-f'];
        $arguments = CommandLineArguments::fromTokens($tokens);
        $serialized = $arguments->toString();

        $this->assertSame($tokens, CommandLineArguments::fromString($serialized)->toTokens());
    }

    public function test_it_removes_repeated_array_options(): void
    {
        $arguments = CommandLineArguments::fromTokens(['--group=A', '--group', 'B', 'tests/Foo.php']);
        $filtered = $arguments->withoutOptions(new InputOption('group', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY));

        $this->assertSame(['tests/Foo.php'], $filtered->toTokens());
    }

    public function test_it_uses_known_option_definitions_to_distinguish_values_from_options(): void
    {
        $filter = new InputOption('filter', null, InputOption::VALUE_REQUIRED);
        $original = CommandLineArguments::fromTokens(['--testsuite', '--filter', 'tests/Foo.php']);
        $arguments = $original->withOptionDefinitions(new InputOption('testsuite', null, InputOption::VALUE_REQUIRED));

        $this->assertFalse($arguments->hasOption($filter));
        $this->assertSame($arguments->toTokens(), $arguments->withoutOptions($filter)->toTokens());
        $this->assertTrue($original->hasOption($filter));
    }

    public function test_it_retains_option_definitions_when_transforming_arguments(): void
    {
        $filter = new InputOption('filter', null, InputOption::VALUE_REQUIRED);
        $original = CommandLineArguments::fromTokens(['--testsuite', '--', '--filter', 'Foo', '--', 'tests/Foo.php']);
        $arguments = $original
            ->withOptionDefinitions(new InputOption('testsuite', null, InputOption::VALUE_REQUIRED))
            ->withoutOptions($filter)
            ->prepend(['--colors=always'])
            ->append(['--log-junit=/tmp/junit.xml'])
            ->append(['--coverage-xml=/tmp/coverage'])
        ;

        $this->assertSame([
            '--colors=always',
            '--testsuite',
            '--',
            '--log-junit=/tmp/junit.xml',
            '--coverage-xml=/tmp/coverage',
            '--',
            'tests/Foo.php',
        ], $arguments->toTokens());
        $this->assertFalse($arguments->hasOption($filter));
    }
}
