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

namespace Infection\Tests\Command\Debug\ListSymbolsCommand;

use Infection\Command\Debug\ListSymbolsCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\Tests\FileSystem\FileSystemTestCase;
use Infection\Tests\TestingUtility\FS;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('integration')]
#[CoversClass(ListSymbolsCommand::class)]
final class ListSymbolsCommandTest extends FileSystemTestCase
{
    #[DataProvider('declarationsProvider')]
    public function test_it_lists_recognized_source_symbols(
        string $contents,
        string $expected,
    ): void {
        FS::dumpFile('symbols.php', $contents);

        $application = new Application(
            Container::create(),
        );
        $commandTester = new CommandTester(
            $application->find('debug:list-symbols'),
        );

        $commandTester->execute(
            ['file' => './unused/../symbols.php'],
            ['capture_stderr_separately' => true],
        );

        $commandTester->assertCommandIsSuccessful();
        $this->assertSame(
            $expected,
            $commandTester->getDisplay(normalize: true),
        );
        $this->assertSame(
            '',
            $commandTester->getErrorOutput(normalize: true),
        );
    }

    public static function declarationsProvider(): iterable
    {
        yield 'global class and method visibility' => [
            <<<'PHP'
                <?php
                class Example {
                    public function Z() {}
                    protected function b() {}
                    private static function a() {}
                }
                PHP,
            "Example\nExample::Z\nExample::b\nExample::a\n",
        ];

        yield 'production traversal skips interfaces and abstract methods' => [
            <<<'PHP'
                <?php
                namespace App;

                interface Contract { public function run(); }
                trait Behavior { public function shared() {} }
                abstract class ParentClass {
                    abstract public function abstractMethod();
                    public function inheritedOnly() {}
                }
                class Child extends ParentClass implements Contract {
                    use Behavior { shared as importedAlias; }
                    public function __construct(public string $property) {}
                    public function run() {}
                    public function abstractMethod() {}
                }
                enum Status: string {
                    case Ready = 'ready';
                    public function label() {}
                }
                PHP,
            <<<'STDOUT'
                App\Behavior
                App\Behavior::shared
                App\ParentClass
                App\ParentClass::inheritedOnly
                App\Child
                App\Child::__construct
                App\Child::run
                App\Child::abstractMethod
                App\Status
                App\Status::label

                STDOUT,
        ];

        yield 'multiple unbracketed namespaces preserve declaration names' => [
            <<<'PHP'
                <?php
                namespace Z;
                use Vendor\External as AliasName;
                class Example extends AliasName {}
                namespace A\B;
                class Example {}
                PHP,
            "Z\\Example\nA\\B\\Example\n",
        ];

        yield 'bracketed namespaces including the global namespace' => [
            <<<'PHP'
                <?php
                namespace App {
                    class Example {}
                }
                namespace {
                    class Example {}
                }
                PHP,
            "App\\Example\nExample\n",
        ];

        yield 'nested and conditional declarations remain in source order' => [
            <<<'PHP'
                <?php
                namespace App;
                class Outer {
                    public function first() {
                        class Inner { public function nested() {} }
                        new class { public function hidden() {} };
                    }
                    public function last() {}
                }
                if (false) { class Conditional {} }
                function declareClass() { class InFunction {} }
                PHP,
            <<<'STDOUT'
                App\Outer
                App\Outer::first
                App\Inner
                App\Inner::nested
                App\Outer::last
                App\Conditional
                App\InFunction

                STDOUT,
        ];

        yield 'anonymous classes and unsupported declarations are omitted' => [
            <<<'PHP'
                <?php
                const GLOBAL_CONSTANT = 1;
                function helper() {}
                $closure = function () {};
                $arrow = fn () => 1;
                new class { public function hidden() {} };
                class Example {
                    const CLASS_CONSTANT = 1;
                    public string $property;
                }
                PHP,
            "Example\n",
        ];

        yield 'empty result' => ['<?php', ''];

        yield 'only unsupported declarations' => [
            '<?php interface Contract { public function run(); }',
            '',
        ];

        yield 'recognition does not require eligible mutation candidates' => [
            <<<'PHP'
                <?php
                /** @infection-ignore-all */
                class Ignored {
                    public function ignored() { return 1; }
                }
                class EmptyClass {}
                PHP,
            "Ignored\nIgnored::ignored\nEmptyClass\n",
        ];

        yield 'the inspected file is never executed or autoloaded' => [
            <<<'PHP'
                <?php
                throw new \RuntimeException('Do not execute');
                class Example extends \Missing\ParentClass {
                    use \Missing\Behavior;
                }
                PHP,
            "Example\n",
        ];
    }

    /**
     * @param array<string, string> $arguments
     */
    #[DataProvider('invalidInputProvider')]
    public function test_it_reports_invalid_input_on_stderr(
        array $arguments,
        string $expectedDiagnostic,
    ): void {
        FS::dumpFile(
            'invalid.php',
            "<?php\nclass Valid {}\nclass Broken {",
        );

        $application = new Application(
            Container::create(),
        );
        $application->setAutoExit(false);
        $tester = new ApplicationTester($application);

        $tester->run(
            [
                'command' => 'debug:list-symbols',
                ...$arguments,
            ],
            ['capture_stderr_separately' => true],
        );

        $this->assertSame(
            1,
            $tester->getStatusCode(),
            'Invalid input should fail the command.',
        );
        $this->assertSame(
            '',
            $tester->getDisplay(normalize: true),
            'Invalid input should not produce partial symbol output on stdout.',
        );
        $this->assertStringContainsString(
            $expectedDiagnostic,
            $tester->getErrorOutput(normalize: true),
            'The failure should be explained on stderr.',
        );
    }

    public static function invalidInputProvider(): iterable
    {
        yield 'missing argument' => [
            [],
            'Not enough arguments (missing: "file").',
        ];

        yield 'blank path' => [
            ['file' => ' '],
            'Expected the argument "file" to be a non-empty file path.',
        ];

        yield 'non-existent path' => [
            ['file' => 'missing.php'],
            'Expected "missing.php" to be a readable file path.',
        ];

        yield 'directory path' => [
            ['file' => '.'],
            'Expected "." to be a readable file path.',
        ];

        yield 'invalid PHP' => [
            ['file' => 'invalid.php'],
            'Check if it is a valid PHP file',
        ];
    }
}
